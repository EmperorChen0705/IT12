<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CleanBackups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old backup files based on retention policy';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning old backups...');

        $backupPath = config('backup.path');
        $retentionDays = config('backup.retention_days', 7);

        if (!File::exists($backupPath)) {
            $this->warn('Backup directory does not exist.');
            return 0;
        }

        // Get all backup files
        $files = File::files($backupPath);
        $deletedCount = 0;
        $deletedSize = 0;

        $cutoffDate = Carbon::now()->subDays($retentionDays);

        foreach ($files as $file) {
            $fileTime = Carbon::createFromTimestamp(File::lastModified($file));

            if ($fileTime->lt($cutoffDate)) {
                $fileSize = File::size($file);
                $deletedSize += $fileSize;

                File::delete($file);
                $deletedCount++;

                $this->info("  Deleted: " . basename($file) . " (created " . $fileTime->diffForHumans() . ")");
            }
        }

        if ($deletedCount > 0) {
            $deletedSizeMB = round($deletedSize / 1024 / 1024, 2);
            $this->info("✓ Cleaned {$deletedCount} old backup(s), freed {$deletedSizeMB} MB");
        } else {
            $this->info('✓ No old backups to clean');
        }

        // Show remaining backups
        $remainingFiles = File::files($backupPath);
        $this->info("Remaining backups: " . count($remainingFiles));

        return 0;
    }
}
