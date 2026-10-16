<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | How many backups of each type to keep. After a backup of a given type
    | is created, App\Services\BackupService prunes older ones of that same
    | type beyond this count — a daily backup never counts against the
    | weekly/monthly retention and vice versa.
    |
    */

    'keep' => [
        'daily' => 5,
        'weekly' => 2,
        'monthly' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Disk
    |--------------------------------------------------------------------------
    |
    | Backups are stored on this filesystem disk (see config/filesystems.php)
    | — deliberately not the "public" disk, since a backup contains the
    | whole database (customer names, emails, phone numbers, addresses) and
    | must only ever be reachable through an authenticated admin download.
    |
    */

    'disk' => 'backups',

];
