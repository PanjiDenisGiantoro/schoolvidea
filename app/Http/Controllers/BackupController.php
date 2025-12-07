<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use ZipArchive;

class BackupController extends Controller
{
    protected $backupPath = 'backups';

    public function index()
    {
        // Get all backup files
        $backups = $this->getBackupFiles();

        // Get backup schedule settings
        $schedule = DB::table('backup_schedules')->first();

        return view('pages.backup.index', compact('backups', 'schedule'));
    }

    public function manualBackup(Request $request)
    {
        $type = $request->type ?? 'full'; // full, weekly, monthly

        try {
            $filename = $this->createBackup($type);

            return response()->json([
                'success' => true,
                'message' => 'Backup berhasil dibuat!',
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat backup: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createBackup($type = 'manual')
    {
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename = "backup_{$type}_{$timestamp}.sql";
        $filepath = storage_path("app/{$this->backupPath}/{$filename}");

        // Create backup directory if not exists
        if (!file_exists(storage_path("app/{$this->backupPath}"))) {
            mkdir(storage_path("app/{$this->backupPath}"), 0755, true);
        }

        // Get database credentials
        $host = env('DB_HOST');
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');

        // Create mysqldump command
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        // Execute backup
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception('Backup gagal dieksekusi');
        }

        // Compress the backup file
        $zipFilename = str_replace('.sql', '.zip', $filename);
        $zipFilepath = storage_path("app/{$this->backupPath}/{$zipFilename}");

        $zip = new ZipArchive();
        if ($zip->open($zipFilepath, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($filepath, $filename);
            $zip->close();

            // Delete original SQL file
            unlink($filepath);
        }

        // Log backup
        DB::table('backup_logs')->insert([
            'filename' => $zipFilename,
            'type' => $type,
            'size' => filesize($zipFilepath),
            'created_at' => now(),
            'created_by' => auth()->id()
        ]);

        return $zipFilename;
    }

    public function download($filename)
    {
        $filepath = storage_path("app/{$this->backupPath}/{$filename}");

        if (!file_exists($filepath)) {
            return back()->with('error', 'File backup tidak ditemukan');
        }

        return response()->download($filepath);
    }

    public function delete($filename)
    {
        $filepath = storage_path("app/{$this->backupPath}/{$filename}");

        if (file_exists($filepath)) {
            unlink($filepath);

            // Delete from log
            DB::table('backup_logs')->where('filename', $filename)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Backup berhasil dihapus'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'File tidak ditemukan'
        ], 404);
    }

    public function updateSchedule(Request $request)
    {
        $request->validate([
            'auto_backup' => 'required|boolean',
            'backup_frequency' => 'required|in:daily,weekly,monthly',
            'backup_time' => 'required',
            'keep_backups' => 'required|integer|min:1|max:365',
            'email_notification' => 'boolean',
            'notification_email' => 'nullable|email'
        ]);

        DB::table('backup_schedules')->updateOrInsert(
            ['id' => 1],
            [
                'auto_backup' => $request->auto_backup,
                'backup_frequency' => $request->backup_frequency,
                'backup_time' => $request->backup_time,
                'keep_backups' => $request->keep_backups,
                'email_notification' => $request->email_notification ?? false,
                'notification_email' => $request->notification_email,
                'updated_at' => now(),
                'updated_by' => auth()->id()
            ]
        );

        return back()->with('success', 'Konfigurasi backup berhasil disimpan');
    }

    public function cleanOldBackups()
    {
        $schedule = DB::table('backup_schedules')->first();
        $keepDays = $schedule->keep_backups ?? 30;

        $backups = $this->getBackupFiles();
        $deletedCount = 0;

        foreach ($backups as $backup) {
            $fileAge = Carbon::parse($backup['date'])->diffInDays(now());

            if ($fileAge > $keepDays) {
                $filepath = storage_path("app/{$this->backupPath}/{$backup['filename']}");
                if (file_exists($filepath)) {
                    unlink($filepath);
                    DB::table('backup_logs')->where('filename', $backup['filename'])->delete();
                    $deletedCount++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} backup lama berhasil dihapus"
        ]);
    }

    private function getBackupFiles()
    {
        $backupDir = storage_path("app/{$this->backupPath}");

        if (!file_exists($backupDir)) {
            return [];
        }

        $files = scandir($backupDir);
        $backups = [];

        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'zip') {
                $filepath = $backupDir . '/' . $file;
                $log = DB::table('backup_logs')->where('filename', $file)->first();

                $backups[] = [
                    'filename' => $file,
                    'size' => filesize($filepath),
                    'date' => date('Y-m-d H:i:s', filemtime($filepath)),
                    'type' => $log->type ?? 'manual',
                    'created_by' => $log->created_by ?? null
                ];
            }
        }

        // Sort by date descending
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $backups;
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|string'
        ]);

        $filename = $request->backup_file;
        $filepath = storage_path("app/{$this->backupPath}/{$filename}");

        if (!file_exists($filepath)) {
            return back()->with('error', 'File backup tidak ditemukan');
        }

        try {
            // Extract ZIP file
            $zip = new ZipArchive();
            $extractPath = storage_path('app/temp_restore');

            if (!file_exists($extractPath)) {
                mkdir($extractPath, 0755, true);
            }

            if ($zip->open($filepath) === TRUE) {
                $zip->extractTo($extractPath);
                $zip->close();
            }

            // Find SQL file
            $sqlFile = null;
            $files = scandir($extractPath);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
                    $sqlFile = $extractPath . '/' . $file;
                    break;
                }
            }

            if (!$sqlFile || !file_exists($sqlFile)) {
                throw new \Exception('File SQL tidak ditemukan dalam backup');
            }

            // Get database credentials
            $host = env('DB_HOST');
            $database = env('DB_DATABASE');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');

            // Restore database
            $command = sprintf(
                'mysql --user=%s --password=%s --host=%s %s < %s',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($database),
                escapeshellarg($sqlFile)
            );

            exec($command, $output, $returnVar);

            // Clean up temp files
            unlink($sqlFile);
            rmdir($extractPath);

            if ($returnVar !== 0) {
                throw new \Exception('Restore gagal dieksekusi');
            }

            return back()->with('success', 'Database berhasil di-restore dari backup');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal restore database: ' . $e->getMessage());
        }
    }
}
