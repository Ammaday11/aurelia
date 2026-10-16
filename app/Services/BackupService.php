<?php

namespace App\Services;

use App\Exceptions\BackupException;
use Carbon\CarbonImmutable;
use Generator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Creates, lists, prunes, downloads, and restores full application
 * backups — the database plus every file on the "public" disk (product/
 * flower/wrapping-paper/ribbon photos, receipts, the logo, etc).
 *
 * A backup is a single zip on the "backups" disk (private — see
 * config/filesystems.php) named "{type}-{Y-m-d_His}.zip", containing:
 *   - manifest.json        {driver, type, created_at}
 *   - database.sqlite       (sqlite connections)  — or —
 *   - database.sql          (mysql/mariadb connections — a plain SQL dump
 *                            generated in pure PHP, not by shelling out to
 *                            mysqldump/mysql, which often isn't installed
 *                            on shared/budget hosting; see dumpMysqlToFile()
 *                            / restoreMysqlDatabase())
 *   - public/...            a full copy of storage/app/public
 *
 * Restoring is necessarily destructive (it replaces the live database
 * and the live public-storage tree), so every restore first takes its
 * own safety snapshot — see restore()/restoreFromUpload() — before
 * touching anything, specifically so a restore gone wrong is itself
 * recoverable the same way.
 */
class BackupService
{
    public const TYPES = ['daily', 'weekly', 'monthly', 'manual', 'pre-restore'];

    protected const FILENAME_PATTERN = '/^(daily|weekly|monthly|manual|pre-restore)-(\d{4}-\d{2}-\d{2}_\d{6})\.zip$/';

    protected function disk()
    {
        return Storage::disk(config('backup.disk'));
    }

    /**
     * Resolved through the "public" disk rather than hardcoding
     * storage_path('app/public') directly — besides being the correct way
     * to respect a non-default disk config, it's what makes this testable
     * with Storage::fake('public') at all.
     */
    protected function publicDiskPath(): string
    {
        return rtrim(Storage::disk('public')->path(''), '/');
    }

    /**
     * Builds a new backup zip of the given type and returns it. Does not
     * prune — callers that want retention enforced call prune() too (the
     * scheduled command does; a manual "Backup Now" from the admin screen
     * does not, since retention is defined per *scheduled* type and a
     * manual backup isn't one).
     */
    public function create(string $type): BackupFileInfo
    {
        if (! in_array($type, self::TYPES, true)) {
            throw new BackupException("Unknown backup type \"{$type}\".");
        }

        File::ensureDirectoryExists($this->disk()->path(''));
        $filename = $type.'-'.now()->format('Y-m-d_His').'.zip';
        $absolutePath = $this->disk()->path($filename);

        $zip = new ZipArchive;
        if ($zip->open($absolutePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new BackupException('Could not create the backup archive.');
        }

        $driver = config('database.default');
        $tempDumpFile = null;

        try {
            $zip->addFromString('manifest.json', json_encode([
                'driver' => $driver,
                'type' => $type,
                'created_at' => now()->toIso8601String(),
            ], JSON_PRETTY_PRINT));

            $tempDumpFile = $this->addDatabaseToZip($zip, $driver);
            $this->addDirectoryToZip($zip, $this->publicDiskPath(), 'public');
        } finally {
            $zip->close();

            // addFile() only reads its source once the zip is closed, so
            // a temp dump file (mysql — see addDatabaseToZip()) can only
            // be cleaned up after that, not right after it's added.
            if ($tempDumpFile && is_file($tempDumpFile)) {
                @unlink($tempDumpFile);
            }
        }

        return new BackupFileInfo($filename, $type, CarbonImmutable::now(), (int) filesize($absolutePath));
    }

    /**
     * Deletes the oldest backups of $type beyond config('backup.keep').
     */
    public function prune(string $type): void
    {
        $keep = config("backup.keep.{$type}");

        if (! $keep) {
            return;
        }

        $this->list()
            ->where('type', $type)
            ->values()
            ->slice($keep)
            ->each(fn (BackupFileInfo $backup) => $this->disk()->delete($backup->filename));
    }

    /**
     * Every backup on disk, newest first within each type (the admin view
     * groups by type itself).
     */
    public function list(): Collection
    {
        return collect($this->disk()->files())
            ->map(fn (string $path) => $this->parse(basename($path)))
            ->filter()
            ->sortByDesc(fn (BackupFileInfo $b) => $b->createdAt->timestamp)
            ->values();
    }

    public function delete(string $filename): void
    {
        $this->assertKnownFilename($filename);
        $this->disk()->delete($filename);
    }

    public function downloadResponse(string $filename)
    {
        $this->assertKnownFilename($filename);

        if (! $this->disk()->exists($filename)) {
            throw new BackupException('That backup no longer exists.');
        }

        return $this->disk()->download($filename);
    }

    /**
     * Restores from a backup already sitting on the backups disk (one
     * taken automatically on schedule, or via "Backup Now").
     */
    public function restore(string $filename): void
    {
        $this->assertKnownFilename($filename);

        if (! $this->disk()->exists($filename)) {
            throw new BackupException('That backup no longer exists.');
        }

        $this->performRestore($this->disk()->path($filename));
    }

    /**
     * Restores from a .zip the admin uploads from their own machine — the
     * "restore from a downloaded backup" path, e.g. after moving to a new
     * server, or restoring a copy kept outside this app entirely.
     */
    public function restoreFromUpload(UploadedFile $file): void
    {
        $tempPath = $file->getRealPath();

        if (! $tempPath) {
            throw new BackupException('The uploaded file could not be read.');
        }

        $this->performRestore($tempPath);
    }

    protected function parse(string $filename): ?BackupFileInfo
    {
        if (! preg_match(self::FILENAME_PATTERN, $filename, $m)) {
            return null;
        }

        return new BackupFileInfo(
            $filename,
            $m[1],
            CarbonImmutable::createFromFormat('Y-m-d_His', $m[2]),
            (int) $this->disk()->size($filename),
        );
    }

    /**
     * Rejects anything that isn't exactly one of our own generated
     * filenames — the only input here is route/request data, so this is
     * the path-traversal guard (no "../", no absolute paths) as well as
     * a basic sanity check that this is actually a backup we made.
     */
    protected function assertKnownFilename(string $filename): void
    {
        if (! preg_match(self::FILENAME_PATTERN, $filename)) {
            throw new BackupException('Not a recognized backup filename.');
        }
    }

    /**
     * Adds the database to the zip, returning the path of a temp file the
     * caller must delete once the zip is closed (mysql only — a sqlite
     * backup adds the live file directly and needs no temp copy).
     */
    protected function addDatabaseToZip(ZipArchive $zip, string $driver): ?string
    {
        if ($driver === 'sqlite') {
            $path = config('database.connections.sqlite.database');

            if (! is_file($path)) {
                throw new BackupException('The SQLite database file could not be found.');
            }

            $zip->addFile($path, 'database.sqlite');

            return null;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $tempFile = tempnam(sys_get_temp_dir(), 'aurelia-db-dump-');
            $this->dumpMysqlToFile($tempFile);
            $zip->addFile($tempFile, 'database.sql');

            return $tempFile;
        }

        throw new BackupException("Backups aren't supported for the \"{$driver}\" database driver.");
    }

    /**
     * A plain-SQL dump of every table — schema (DROP + the exact CREATE
     * TABLE MySQL itself reports) then data as batched multi-row INSERTs
     * — written straight to $path rather than built up as one giant PHP
     * string, so a shop's worth of order history doesn't have to fit in
     * memory twice over. Pure PHP/PDO: no mysqldump binary required,
     * since plenty of shared hosting doesn't have one on the PATH.
     */
    protected function dumpMysqlToFile(string $path): void
    {
        $handle = fopen($path, 'w');

        if (! $handle) {
            throw new BackupException('Could not open a temporary file for the database dump.');
        }

        try {
            $pdo = DB::connection()->getPdo();
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            $tables = collect(DB::select('SHOW TABLES'))->map(fn ($row) => array_values((array) $row)[0]);

            foreach ($tables as $table) {
                $createTable = DB::select("SHOW CREATE TABLE `{$table}`")[0]->{'Create Table'};
                fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n{$createTable};\n\n");

                // Not chunkById() — that pages by an "id" column, but not
                // every table has one (password_reset_tokens is keyed by
                // email, sessions by a string token), and offset paging
                // needs a deterministic order anyway. A shop's worth of
                // rows per table fits in memory at once without issue;
                // only the write to disk is batched, via chunk() below.
                $rows = DB::table($table)->get();

                foreach ($rows->chunk(500) as $chunk) {
                    $columns = array_keys((array) $chunk->first());
                    $columnList = implode(', ', array_map(fn ($c) => "`{$c}`", $columns));

                    $valueRows = $chunk->map(function ($row) use ($pdo, $columns) {
                        $row = (array) $row;
                        $values = array_map(
                            fn ($column) => is_null($row[$column]) ? 'NULL' : $pdo->quote((string) $row[$column]),
                            $columns
                        );

                        return '('.implode(', ', $values).')';
                    })->implode(",\n");

                    fwrite($handle, "INSERT INTO `{$table}` ({$columnList}) VALUES\n{$valueRows};\n\n");
                }
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        } finally {
            fclose($handle);
        }
    }

    protected function addDirectoryToZip(ZipArchive $zip, string $directory, string $zipPrefix): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $zip->addEmptyDir($zipPrefix);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $relative = $zipPrefix.'/'.substr($file->getPathname(), strlen($directory) + 1);

            if ($file->isDir()) {
                $zip->addEmptyDir($relative);
            } else {
                $zip->addFile($file->getPathname(), $relative);
            }
        }
    }

    /**
     * The actual restore, shared by restore() and restoreFromUpload().
     * $zipPath is any local, readable path — the backups disk or a just-
     * uploaded temp file both work since this only ever reads from it.
     */
    protected function performRestore(string $zipPath): void
    {
        // Safety net first, and if it fails, abort rather than proceeding
        // without one — a failed restore should never also cost the
        // admin their only way back to the pre-restore state.
        try {
            $safety = $this->create('pre-restore');
            $this->prunePreRestoreSafetyBackups();
        } catch (\Throwable $e) {
            throw new BackupException('Could not take a safety backup before restoring — restore cancelled. '.$e->getMessage());
        }

        $tempDir = storage_path('app/tmp/restore-'.Str::uuid());
        File::ensureDirectoryExists($tempDir);

        try {
            $zip = new ZipArchive;

            if ($zip->open($zipPath) !== true) {
                throw new BackupException('That file is not a valid backup archive.');
            }

            $zip->extractTo($tempDir);
            $zip->close();

            $manifestPath = $tempDir.'/manifest.json';
            $manifest = is_file($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : null;

            if (! $manifest || ! isset($manifest['driver'])) {
                throw new BackupException('That archive doesn\'t look like an Aurelia Boutonnière backup (missing manifest.json).');
            }

            $currentDriver = config('database.default');
            $backupDriver = $manifest['driver'] === 'mariadb' ? 'mysql' : $manifest['driver'];
            $normalizedCurrent = $currentDriver === 'mariadb' ? 'mysql' : $currentDriver;

            if ($backupDriver !== $normalizedCurrent) {
                throw new BackupException(
                    "This backup was taken from a \"{$manifest['driver']}\" database, but this install is running \"{$currentDriver}\" — restoring across different database engines isn't supported."
                );
            }

            $this->restoreDatabase($tempDir, $currentDriver);
            $this->restorePublicFiles($tempDir);

            // The restored database may predate migrations that have
            // since been added — bring its schema up to the code's
            // current expectations.
            Artisan::call('migrate', ['--force' => true]);
        } finally {
            File::deleteDirectory($tempDir);
        }

        Log::info('Backup restored.', ['safety_backup' => $safety->filename]);
    }

    protected function restoreDatabase(string $tempDir, string $driver): void
    {
        if ($driver === 'sqlite') {
            $source = $tempDir.'/database.sqlite';

            if (! is_file($source)) {
                throw new BackupException('That archive is missing database.sqlite.');
            }

            // The live file has to be released before it can be
            // overwritten — not needed for mysql below, which stays on
            // the same already-open connection throughout.
            DB::disconnect();

            $destination = config('database.connections.sqlite.database');
            File::copy($source, $destination);

            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $source = $tempDir.'/database.sql';

            if (! is_file($source)) {
                throw new BackupException('That archive is missing database.sql.');
            }

            $this->restoreMysqlDatabase($source);

            return;
        }

        throw new BackupException("Restoring isn't supported for the \"{$driver}\" database driver.");
    }

    /**
     * Executes dumpMysqlToFile()'s output statement by statement via PDO
     * — pure PHP, same reasoning as the dump side: no mysql CLI binary
     * required. Splitting on a bare `;` would be wrong (a value like an
     * order's special_notes could itself contain "foo;\nbar"), so
     * splitSqlStatements() tracks quote state char by char and only
     * splits on a `;` that's actually outside a string.
     */
    protected function restoreMysqlDatabase(string $path): void
    {
        $pdo = DB::connection()->getPdo();
        $sql = file_get_contents($path);

        foreach ($this->splitSqlStatements($sql) as $statement) {
            $pdo->exec($statement);
        }
    }

    protected function splitSqlStatements(string $sql): Generator
    {
        $length = strlen($sql);
        $buffer = '';
        $inString = false;
        $quoteChar = '';

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            if ($inString) {
                $buffer .= $char;

                if ($char === '\\' && $i + 1 < $length) {
                    $buffer .= $sql[++$i];

                    continue;
                }

                if ($char === $quoteChar) {
                    $inString = false;
                }

                continue;
            }

            if ($char === "'" || $char === '"' || $char === '`') {
                $inString = true;
                $quoteChar = $char;
                $buffer .= $char;

                continue;
            }

            if ($char === ';') {
                $statement = trim($buffer);

                if ($statement !== '') {
                    yield $statement;
                }

                $buffer = '';

                continue;
            }

            $buffer .= $char;
        }

        $remaining = trim($buffer);

        if ($remaining !== '') {
            yield $remaining;
        }
    }

    /**
     * Makes storage/app/public exactly match the backup's public/ folder
     * — removing anything not in the backup too, since a restore means
     * "go back to this exact state," not "layer old files on top of
     * whatever's here now."
     */
    protected function restorePublicFiles(string $tempDir): void
    {
        $source = $tempDir.'/public';
        $destination = $this->publicDiskPath();

        if (! is_dir($source)) {
            return;
        }

        File::ensureDirectoryExists($destination);

        foreach (File::allFiles($destination) as $file) {
            File::delete($file->getPathname());
        }
        foreach (File::directories($destination) as $dir) {
            File::deleteDirectory($dir);
        }

        File::copyDirectory($source, $destination);
    }

    /**
     * The automatic safety backup taken before every restore (see
     * performRestore()) isn't one of the scheduled daily/weekly/monthly
     * types, so it has no config('backup.keep') entry of its own — it
     * would otherwise accumulate forever across repeated restores. Keep
     * the same count as daily backups.
     */
    protected function prunePreRestoreSafetyBackups(): void
    {
        $keep = config('backup.keep.daily');

        $this->list()
            ->where('type', 'pre-restore')
            ->values()
            ->slice($keep)
            ->each(fn (BackupFileInfo $backup) => $this->disk()->delete($backup->filename));
    }
}
