<?php
namespace App\Jobs;

use App\Imports\RoleImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportRoleJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected $file;

    /**
     * Create a new job instance.
     *
     * @param string $file
     * @return void
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Mengimpor data menggunakan RoleImport
            Excel::import(new RoleImport(), $this->file);
            Log::info('Role import berhasil');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengimpor role: ' . $e->getMessage());
        }
    }
}
