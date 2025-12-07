<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\BackupController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {type=scheduled}';
    protected $description = 'Backup database automatically';

    public function handle()
    {
        $type = $this->argument('type');

        $this->info('Starting database backup...');

        try {
            $controller = new BackupController();
            $filename = $controller->createBackup($type);

            $this->info("Backup created successfully: {$filename}");

            // Send email notification if enabled
            $schedule = DB::table('backup_schedules')->first();

            if ($schedule && $schedule->email_notification && $schedule->notification_email) {
                $this->sendNotification($filename, $schedule->notification_email);
            }

            // Clean old backups
            $controller->cleanOldBackups();

            return 0;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return 1;
        }
    }

    private function sendNotification($filename, $email)
    {
        try {
            Mail::raw(
                "Database backup berhasil dibuat.\n\nFilename: {$filename}\nWaktu: " . now()->format('Y-m-d H:i:s'),
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Database Backup Notification');
                }
            );

            $this->info('Email notification sent to: ' . $email);
        } catch (\Exception $e) {
            $this->warn('Failed to send email notification: ' . $e->getMessage());
        }
    }
}
