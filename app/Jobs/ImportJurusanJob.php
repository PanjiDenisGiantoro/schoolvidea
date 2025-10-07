<?php

namespace App\Jobs;

use App\Imports\JurusanImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportJurusanJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

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
            Excel::import(new JurusanImport($this->unit_id, $this->tahun_ajaran_id), $this->file);
            Log::info('Jurusan import berhasil');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengimpor jurusan: ' . $e->getMessage());
        }
    }
}
