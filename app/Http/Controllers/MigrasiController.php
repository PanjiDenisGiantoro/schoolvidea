<?php

namespace App\Http\Controllers;

use App\Exports\JurusanExport;
use App\Exports\KelasExport;
use App\Exports\OfficerExport;
use App\Exports\OfficerTemplateExport;
use App\Exports\SiswaExport;
use App\Imports\KelasImport;
use App\Imports\OfficerImport;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Officer;
use App\Models\Siswa;
use App\Models\Tahun_ajaran;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class MigrasiController extends Controller
{
    public function index()
    {
        $units = \App\Models\Unit::where('status', '1')->get();

        $unit_migrasi = Unit::when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->where('id', $unitId);
        })->where('status','1')->get();

        $tahun_ajaran = Tahun_ajaran::orderBy('id','desc')->get();
        // siapkan array hasil
        $totals = [];

        foreach ($units as $unit) {
            $totals[] = [
                'unit'    => $unit->nama_unit,
                'siswa'   => \App\Models\Siswa::where('unit_id', $unit->id)->count(),
                'kelas'   => \App\Models\Kelas::where('unit_id', $unit->id)->count(),
                'officer' => \App\Models\Officer::where('unit_id', $unit->id)->count(),
                'jurusan' => \App\Models\Jurusan::where('unit_id', $unit->id)->count(),
            ];
        }
        return view('pages.migrasi.migrasi',compact('totals','unit_migrasi','tahun_ajaran'));
    }
    public function downloadTemplate($type)
    {
        $filePath = public_path("template/{$type}_template.xlsx");

        if(file_exists($filePath)){
            return response()->download($filePath);
        }
        return back()->with('error', 'Template tidak ditemukan');
    }
    public function importSiswa(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);
        // logic import siswa
        return back()->with('success', 'Data siswa berhasil diimport!');
    }

    public function importKelas(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        $unit_id = $request->input('unit_id');
        $tahun_ajaran_id = $request->input('tahun_ajaran_id');

        Excel::import(new KelasImport($unit_id, $tahun_ajaran_id), $request->file('file'));

        // logic import kelas
        return back()->with('success', 'Data kelas berhasil diimport!');
    }

    public function importOfficer(Request $request)
    {
        // Validate the file
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            // Import the file and get the imported count
            $import =  Excel::import(new OfficerImport($request->unit_id, $request->tahun_ajaran_id), $request->file('file'));

            // Retrieve the imported count

            // Return a success message with the count
            return back()->with('success', 'Data imported successfully.');
        } catch (\Exception $e) {
            return back()->with('danger', 'Failed to import: ' . $e->getMessage());
        }
    }


    public function importJurusan(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        $unit_id = $request->input('unit_id');
        $tahun_ajaran_id = $request->input('tahun_ajaran_id');

        Excel::import(new \App\Imports\JurusanImport($unit_id, $tahun_ajaran_id), $request->file('file'));

        return back()->with('success', 'Data jurusan berhasil diimport!');
    }


    public function exportOfficer()
    {
        return Excel::download(new OfficerExport(), 'officer.xlsx');
    }
    public function exportSiswa()
    {
        return Excel::download(new SiswaExport(), 'siswa.xlsx');
    }
    public function exportkelas()
    {
        return Excel::download(new KelasExport(), 'kelas.xlsx');
    }
    public function jurusantkelas()
    {
        return Excel::download(new JurusanExport(), 'jurusan.xlsx');
    }

}
