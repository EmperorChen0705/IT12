<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database {--clean : Clean old backups after creating new one}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup of the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database backup...');

        try {
            // Get database configuration
            $connection = config('backup.connection');
            $database = config("database.connections.{$connection}.database");
            $username = config("database.connections.{$connection}.username");
            $password = config("database.connections.{$connection}.password");
            $host = config("database.connections.{$connection}.host");
            $port = config("database.connections.{$connection}.port", 3306);

            // Create backup directory if it doesn't exist
            $backupPath = config('backup.path');
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
                $this->info("Created backup directory: {$backupPath}");
            }

            // Generate backup filename
            $filename = $this->generateFilename($database);
            $filepath = $backupPath . DIRECTORY_SEPARATOR . $filename;

            // Build mysqldump command
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($database),
                escapeshellarg($filepath)
            );

            // Execute backup
            $this->info('Creating backup...');
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                $this->error('Backup failed! Please check your database configuration.');
                return 1;
            }

            // Verify backup was created
            if (!File::exists($filepath)) {
                $this->error('Backup file was not created!');
                return 1;
            }

            $fileSize = File::size($filepath);
            $fileSizeMB = round($fileSize / 1024 / 1024, 2);

            $this->info("✓ Backup created successfully!");
            $this->info("  File: {$filename}");
            $this->info("  Size: {$fileSizeMB} MB");
            $this->info("  Location: {$filepath}");

            // Clean old backups if requested
            if ($this->option('clean')) {
                $this->call('backup:clean');
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Generate backup filename based on configuration
     */
    protected function generateFilename($database)
    {
        $format = config('backup.filename_format');

        $replacements = [
            '{database}' => $database,
            '{date}' => Carbon::now()->format('Y-m-d'),
            '{time}' => Carbon::now()->format('H-i-s'),
            '{timestamp}' => Carbon::now()->timestamp,
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $format
        );
    }
}
