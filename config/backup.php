<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backup Storage Path
    |--------------------------------------------------------------------------
    |
    | This is the path where backups will be stored locally.
    |
    */

    'path' => storage_path('app/backups'),

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | The database connection to backup. This should match a connection
    | defined in your database.php config file.
    |
    */

    'connection' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Backup Retention Days
    |--------------------------------------------------------------------------
    |
    | Number of days to keep backups. Older backups will be automatically
    | deleted during cleanup.
    |
    */

    'retention_days' => 7,

    /*
    |--------------------------------------------------------------------------
    | Backup Naming Convention
    |--------------------------------------------------------------------------
    |
    | The format for backup file names. Available placeholders:
    | {database} - Database name
    | {date} - Current date (Y-m-d)
    | {time} - Current time (H-i-s)
    | {timestamp} - Unix timestamp
    |
    */

    'filename_format' => 'backup-{database}-{date}-{time}.sql',

    /*
    |--------------------------------------------------------------------------
    | Excluded Tables
    |--------------------------------------------------------------------------
    |
    | Tables to exclude from backups. Useful for temporary or cache tables.
    |
    */

    'excluded_tables' => [
        // 'cache',
        // 'sessions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Compression
    |--------------------------------------------------------------------------
    |
    | Whether to compress backup files. Requires gzip to be available.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | MySQL Dump Binary Path
    |--------------------------------------------------------------------------
    |
    | The path to the mysqldump binary. on Windows this is usually in your
    | XAMPP/WAMP/Laragon installation.
    |
    */

    'dump_binary_path' => env('DUMP_BINARY_PATH', 'mysqldump'),

    'compress' => false,

];
