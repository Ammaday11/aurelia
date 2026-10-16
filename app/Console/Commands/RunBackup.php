<?php

namespace App\Console\Commands;

use App\Exceptions\BackupException;
use App\Services\BackupService;
use Illuminate\Console\Command;

/**
 * Scheduled in App\Console\Kernel — one invocation per type, each on its
 * own cadence (daily/weekly/monthly). "manual" exists for the admin
 * screen's "Backup Now" button, which calls BackupService directly
 * rather than this command, but can also be run by hand:
 *   php artisan backup:run manual
 */
class RunBackup extends Command
{
    protected $signature = 'backup:run {type=daily : daily, weekly, monthly, or manual}';

    protected $description = 'Create a full backup (database + uploaded files) and prune old ones of the same type';

    public function handle(BackupService $backups): int
    {
        $type = $this->argument('type');

        try {
            $backup = $backups->create($type);
            $this->info("Backup created: {$backup->filename} ({$backup->humanSize()})");

            $backups->prune($type);

            return self::SUCCESS;
        } catch (BackupException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
