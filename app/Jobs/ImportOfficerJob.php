<?php
namespace App\Jobs;

use App\Imports\OfficerImport1;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportOfficerJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;
    public $timeout = 600; // 10 menit (sesuaikan kebutuhan)
    public $tries = 1; // Jangan retry kalau timeout
    public $maxExceptions = 1;
    protected $unit_id;
    protected $tahun_ajaran_id;
    protected $file;

    /**
     * Create a new job instance.
     *
     * @param int $unit_id
     * @param int $tahun_ajaran_id
     * @param \Illuminate\Http\UploadedFile $file
     * @return void
     */
    public function __construct($unit_id, $tahun_ajaran_id, $file)
    {
        $this->unit_id = $unit_id;
        $this->tahun_ajaran_id = $tahun_ajaran_id;
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

            set_time_limit(0);
            ini_set('max_execution_time', 0);
            // Mengimpor data menggunakan OfficerImport
            Excel::import(new OfficerImport1($this->unit_id, $this->tahun_ajaran_id), $this->file);
            Log::info('Officer import berhasil');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengimpor officer: ' . $e->getMessage());
        }
    }
}
