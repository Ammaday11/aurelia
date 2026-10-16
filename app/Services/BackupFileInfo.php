<?php

namespace App\Services;

use Carbon\CarbonImmutable;

/**
 * One backup zip on the backups disk, parsed from its filename — see
 * BackupService::FILENAME_PATTERN. Deliberately not an Eloquent model:
 * backups are filesystem artifacts, and a database table listing them
 * would itself go stale the moment an admin restores an older backup
 * (rows for anything created after that point would vanish even though
 * the zip files are still sitting right there on disk).
 */
class BackupFileInfo
{
    public function __construct(
        public readonly string $filename,
        public readonly string $type,
        public readonly CarbonImmutable $createdAt,
        public readonly int $size,
    ) {}

    public function humanSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->size;

        foreach ($units as $unit) {
            if ($bytes < 1024 || $unit === end($units)) {
                return ($unit === 'B' ? $bytes : round($bytes, 1)).' '.$unit;
            }

            $bytes /= 1024;
        }

        return $this->size.' B';
    }
}
