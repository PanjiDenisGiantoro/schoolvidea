<?php
namespace App\Jobs;

use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel as ExcelExcel;

class ImportSiswaJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected $unit_id;
    protected $tahun_ajaran_id;
    protected $filePath;

    /**
     * Create a new job instance.
     *
     * @param int $unit_id
     * @param int $tahun_ajaran_id
     * @param \Illuminate\Http\UploadedFile $file
     * @return void
     */
    public function __construct($unit_id, $tahun_ajaran_id, $filePath)
    {
        $this->unit_id = $unit_id;
        $this->tahun_ajaran_id = $tahun_ajaran_id;
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
public function handle()
{
    try {
        // Path full file di storage/app/private/temp/...
        $fullPath = Storage::disk('local')->path($this->filePath);

        if (!file_exists($fullPath)) {
            Log::error('FILE TIDAK DITEMUKAN', ['filePath' => $fullPath]);
            return;
        }

        // Import Excel
        Excel::import(
            new SiswaImport($this->unit_id, $this->tahun_ajaran_id),
            $fullPath,
            null, // tidak perlu disk, karena pakai full path
            ExcelExcel::XLSX
        );

        Log::info('IMPORT SISWA BERHASIL');
    } catch (\Exception $e) {
        Log::error('Import siswa gagal', ['error' => $e->getMessage()]);
    }
}

}