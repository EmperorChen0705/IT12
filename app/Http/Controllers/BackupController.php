<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackupController extends Controller
{
    /**
     * Display a listing of backups.
     */
    public function index()
    {
        $backupPath = config('backup.path');
        $backups = [];

        if (File::exists($backupPath)) {
            $files = File::files($backupPath);

            foreach ($files as $file) {
                $backups[] = [
                    'filename' => basename($file),
                    'path' => $file,
                    'size' => File::size($file),
                    'size_formatted' => $this->formatBytes(File::size($file)),
                    'created_at' => Carbon::createFromTimestamp(File::lastModified($file)),
                    'age' => Carbon::createFromTimestamp(File::lastModified($file))->diffForHumans(),
                ];
            }

            // Sort by creation date, newest first
            usort($backups, function ($a, $b) {
                return $b['created_at']->timestamp - $a['created_at']->timestamp;
            });
        }

        // Calculate total storage used
        $totalSize = array_sum(array_column($backups, 'size'));
        $totalSizeFormatted = $this->formatBytes($totalSize);

        return view('backups.index', compact('backups', 'totalSize', 'totalSizeFormatted'));
    }

    /**
     * Create a new backup.
     */
    public function create()
    {
        try {
            Artisan::call('backup:database', ['--clean' => true]);
            $output = Artisan::output();

            return redirect()->route('backups.index')
                ->with('success', 'Backup created successfully!');
        } catch (\Exception $e) {
            return redirect()->route('backups.index')
                ->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Download a backup file.
     */
    public function download($filename)
    {
        $backupPath = config('backup.path');
        $filepath = $backupPath . DIRECTORY_SEPARATOR . $filename;

        // Security check: ensure file exists and is within backup directory
        if (!File::exists($filepath) || !str_starts_with(realpath($filepath), realpath($backupPath))) {
            abort(404, 'Backup file not found');
        }

        return response()->download($filepath);
    }

    /**
     * Delete a backup file.
     */
    public function destroy($filename)
    {
        $backupPath = config('backup.path');
        $filepath = $backupPath . DIRECTORY_SEPARATOR . $filename;

        // Security check: ensure file exists and is within backup directory
        if (!File::exists($filepath) || !str_starts_with(realpath($filepath), realpath($backupPath))) {
            return redirect()->route('backups.index')
                ->with('error', 'Backup file not found');
        }

        File::delete($filepath);

        return redirect()->route('backups.index')
            ->with('success', 'Backup deleted successfully!');
    }

    /**
     * Restore database from backup.
     */
    public function restore(Request $request, $filename)
    {
        $backupPath = config('backup.path');
        $filepath = $backupPath . DIRECTORY_SEPARATOR . $filename;

        // Security check
        if (!File::exists($filepath) || !str_starts_with(realpath($filepath), realpath($backupPath))) {
            return redirect()->route('backups.index')
                ->with('error', 'Backup file not found');
        }

        try {
            // Get database configuration
            $connection = config('backup.connection');
            $database = config("database.connections.{$connection}.database");
            $username = config("database.connections.{$connection}.username");
            $password = config("database.connections.{$connection}.password");
            $host = config("database.connections.{$connection}.host");
            $port = config("database.connections.{$connection}.port", 3306);

            // Build mysql restore command
            $command = sprintf(
                'mysql --user=%s --password=%s --host=%s --port=%s %s < %s',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($database),
                escapeshellarg($filepath)
            );

            // Execute restore
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                return redirect()->route('backups.index')
                    ->with('error', 'Restore failed! Please check your database configuration.');
            }

            return redirect()->route('backups.index')
                ->with('success', 'Database restored successfully from backup!');

        } catch (\Exception $e) {
            return redirect()->route('backups.index')
                ->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
