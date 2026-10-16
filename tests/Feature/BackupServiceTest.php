<?php

namespace Tests\Feature;

use App\Exceptions\BackupException;
use App\Models\Flower;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

/**
 * Covers App\Services\BackupService end to end against a real sqlite
 * file (the default test environment uses an in-memory ":memory:"
 * database, which the file-copy-based backup/restore logic can't back
 * up — see setUp()). The restore test is the one that actually matters:
 * it proves a backup can be used to undo real data loss, not just that
 * a zip gets created.
 */
class BackupServiceTest extends TestCase
{
    use RefreshDatabase;

    protected string $tempDbPath;

    protected function setUp(): void
    {
        parent::setUp();

        // The default test environment uses an in-memory sqlite database
        // (see phpunit.xml) so every test runs fast — but BackupService
        // copies the database *file*, which ":memory:" doesn't have.
        // Point the sqlite connection at a real temp file instead and
        // re-migrate it, so these tests exercise the actual file-copy
        // code path. DB::purge() drops the already-resolved ":memory:"
        // PDO connection RefreshDatabase just set up, so the next query
        // opens a fresh one against the new path.
        $this->tempDbPath = sys_get_temp_dir().'/aurelia-backup-test-'.Str::random(12).'.sqlite';
        touch($this->tempDbPath);
        config(['database.connections.sqlite.database' => $this->tempDbPath]);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true]);

        Storage::fake('backups');
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        DB::purge('sqlite');
        @unlink($this->tempDbPath);

        parent::tearDown();
    }

    protected function service(): BackupService
    {
        return app(BackupService::class);
    }

    public function test_create_builds_a_zip_containing_the_database_and_public_files(): void
    {
        Storage::disk('public')->put('flowers/sample.png', 'fake-image-bytes');

        $backup = $this->service()->create('daily');

        Storage::disk('backups')->assertExists($backup->filename);
        $this->assertSame('daily', $backup->type);
        $this->assertStringStartsWith('daily-', $backup->filename);

        $zip = new ZipArchive;
        $zip->open(Storage::disk('backups')->path($backup->filename));
        $this->assertNotFalse($zip->locateName('manifest.json'));
        $this->assertNotFalse($zip->locateName('database.sqlite'));
        $this->assertNotFalse($zip->locateName('public/flowers/sample.png'));

        $manifest = json_decode($zip->getFromName('manifest.json'), true);
        $this->assertSame('sqlite', $manifest['driver']);
        $this->assertSame('daily', $manifest['type']);
        $zip->close();
    }

    public function test_prune_keeps_only_the_configured_number_of_each_type(): void
    {
        config(['backup.keep.daily' => 2]);

        // Written straight to the fake disk rather than via create() — the
        // filename's timestamp is all prune() reads, and real zip
        // generation per iteration would make this test needlessly slow.
        foreach (['03', '02', '01'] as $day) {
            Storage::disk('backups')->put("daily-2026-01-{$day}_020000.zip", 'x');
        }

        $this->service()->prune('daily');

        $remaining = $this->service()->list()->where('type', 'daily')->pluck('filename')->values();
        $this->assertCount(2, $remaining);
        $this->assertTrue($remaining->contains('daily-2026-01-03_020000.zip'));
        $this->assertTrue($remaining->contains('daily-2026-01-02_020000.zip'));
        $this->assertFalse($remaining->contains('daily-2026-01-01_020000.zip'));
    }

    public function test_prune_does_not_touch_other_types(): void
    {
        config(['backup.keep.daily' => 1, 'backup.keep.weekly' => 1]);

        Storage::disk('backups')->put('daily-2026-01-02_020000.zip', 'x');
        Storage::disk('backups')->put('daily-2026-01-01_020000.zip', 'x');
        Storage::disk('backups')->put('weekly-2026-01-01_020000.zip', 'x');

        $this->service()->prune('daily');

        Storage::disk('backups')->assertMissing('daily-2026-01-01_020000.zip');
        Storage::disk('backups')->assertExists('daily-2026-01-02_020000.zip');
        Storage::disk('backups')->assertExists('weekly-2026-01-01_020000.zip');
    }

    public function test_restore_reverts_the_database_and_public_files_to_the_backups_state(): void
    {
        Storage::disk('public')->put('flowers/keep.png', 'original-bytes');
        $rose = Flower::create(['name' => 'Rose', 'price_per_stem' => 35, 'stock_quantity' => 10, 'is_active' => true]);

        $backup = $this->service()->create('manual');

        // Change everything after the backup was taken.
        $rose->update(['name' => 'Renamed Rose']);
        Flower::create(['name' => 'Added After Backup', 'price_per_stem' => 10, 'stock_quantity' => 5, 'is_active' => true]);
        Storage::disk('public')->put('flowers/keep.png', 'changed-bytes');
        Storage::disk('public')->put('flowers/new-file.png', 'new-bytes');

        $this->service()->restore($backup->filename);

        $this->assertSame('Rose', Flower::find($rose->id)->name);
        $this->assertDatabaseMissing('flowers', ['name' => 'Added After Backup']);
        $this->assertSame('original-bytes', Storage::disk('public')->get('flowers/keep.png'));
        Storage::disk('public')->assertMissing('flowers/new-file.png');

        // The automatic safety snapshot taken right before the restore.
        $this->assertTrue($this->service()->list()->where('type', 'pre-restore')->isNotEmpty());
    }

    public function test_restore_from_an_uploaded_file_works_the_same_way(): void
    {
        $rose = Flower::create(['name' => 'Rose', 'price_per_stem' => 35, 'stock_quantity' => 10, 'is_active' => true]);
        $backup = $this->service()->create('manual');
        $zipPath = Storage::disk('backups')->path($backup->filename);

        $rose->delete();
        $this->assertDatabaseMissing('flowers', ['id' => $rose->id]);

        $upload = new UploadedFile($zipPath, $backup->filename, 'application/zip', null, true);
        $this->service()->restoreFromUpload($upload);

        $this->assertDatabaseHas('flowers', ['id' => $rose->id, 'name' => 'Rose']);
    }

    public function test_delete_rejects_a_filename_that_is_not_a_recognized_backup(): void
    {
        $this->expectException(BackupException::class);
        $this->service()->delete('../../.env');
    }

    public function test_admin_can_download_a_backup(): void
    {
        $admin = User::factory()->create();
        $backup = $this->service()->create('manual');

        $response = $this->actingAs($admin)->get(route('admin.backups.download', $backup->filename));

        $response->assertOk();
    }

    public function test_guest_cannot_reach_the_backups_screen(): void
    {
        $this->get(route('admin.backups.index'))->assertRedirect(route('login'));
    }

    public function test_the_backup_run_command_creates_and_prunes(): void
    {
        config(['backup.keep.daily' => 1]);
        Storage::disk('backups')->put('daily-2026-01-01_020000.zip', 'x');

        $this->artisan('backup:run', ['type' => 'daily'])->assertExitCode(0);

        $dailies = $this->service()->list()->where('type', 'daily');
        $this->assertCount(1, $dailies);
        Storage::disk('backups')->assertMissing('daily-2026-01-01_020000.zip');
    }
}
