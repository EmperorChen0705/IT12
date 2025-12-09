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
            $configPort = config("database.connections.{$connection}.port", 3306);

            // Handle case where host contains port (e.g. host:port)
            if (strpos($host, ':') !== false) {
                $parts = explode(':', $host);
                $host = $parts[0];
                $port = $parts[1]; // Use port from host string
            } else {
                $port = $configPort; // Use standard config port
            }

            // Create backup directory if it doesn't exist
            $backupPath = config('backup.path');
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
                $this->info("Created backup directory: {$backupPath}");
            }

            // Generate backup filename
            $filename = $this->generateFilename($database);
            $filepath = $backupPath . DIRECTORY_SEPARATOR . $filename;

            // Temp file for error logging
            $errorLogFile = $backupPath . DIRECTORY_SEPARATOR . 'backup_error.log';

            // Resolve mysqldump path
            $configuredPath = config('backup.dump_binary_path');
            $dumpBinaryPath = 'mysqldump'; // Default fallback

            if ($configuredPath && File::exists($configuredPath)) {
                // If a specific valid path is provided, use it
                $dumpBinaryPath = '"' . $configuredPath . '"';
            } elseif ($configuredPath && $configuredPath !== 'mysqldump') {
                // Configured but not found? Log warning and try default
                $this->warn("Configured dump path '$configuredPath' not found. Trying global 'mysqldump'...");
            }

            // Build mysqldump command
            // Stdout to backup file, Stderr to error log
            $command = sprintf(
                '%s --user=%s --password=%s --host=%s --port=%s --ssl-verify-server-cert=0 %s > %s 2> %s',
                $dumpBinaryPath,
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($database),
                escapeshellarg($filepath),
                escapeshellarg($errorLogFile)
            );

            // Execute backup
            $this->info('Creating backup...');
            exec($command, $output, $returnCode);

            // Check for errors
            if ($returnCode !== 0) {
                $errorOutput = File::exists($errorLogFile) ? File::get($errorLogFile) : 'Unknown error';
                $this->error('Backup failed! Please check your database configuration.');
                $this->error('Error details: ' . $errorOutput);

                // Cleanup partial files
                if (File::exists($filepath))
                    File::delete($filepath);
                if (File::exists($errorLogFile))
                    File::delete($errorLogFile);

                return 1;
            }

            // Cleanup error log if empty or specific warnings we ignore
            if (File::exists($errorLogFile)) {
                $errorContent = File::get($errorLogFile);
                if (!empty($errorContent)) {
                    // Sometimes mysqldump outputs warnings to stderr even on success
                    $this->warn('Backup completed with warnings: ' . $errorContent);
                }
                File::delete($errorLogFile);
            }

            // Verify backup was created and has content
            if (!File::exists($filepath)) {
                $this->error('Backup file was not created!');
                return 1;
            }

            if (File::size($filepath) === 0) {
                $this->error('Backup created but file is empty (0 bytes). Check database structure.');
                File::delete($filepath);
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
