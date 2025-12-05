<?php

namespace App\Http\Controllers;

use App\Exports\JurusanExport;
use App\Exports\RoleExport;
use App\Exports\PositionExport;
use App\Jobs\ImportJurusanJob;
use App\Jobs\ImportRoleJob;
use App\Jobs\ImportPositionJob;
use App\Exports\KelasExport;
use App\Exports\OfficerExport;
use App\Exports\OfficerTemplateExport;
use App\Exports\SiswaExport;
use App\Imports\KelasImport;
use App\Imports\OfficerImport;
use App\Imports\SiswaImport;
use App\Jobs\ImportKelasJob;
use App\Jobs\ImportOfficerJob;
use App\Jobs\ImportSiswaJob;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Officer;
use App\Models\Siswa;
use App\Models\Roles;
use App\Models\Positions;
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

        // Ambil user yang sedang login
        $user = Auth::user();

        // Buat query dasar untuk Unit aktif
        $unitsQuery = \App\Models\Unit::where('status', '1');

        // Filter berdasarkan prioritas: yayasan_id > unit_id > admin filter
        if ($user->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan semua unit di yayasan tersebut
            $unitsQuery->where('yayasan_id', $user->yayasan_id);
        } elseif ($user->unit_id) {
            // Jika user punya unit_id, tampilkan hanya unit itu
            $unitsQuery->where('id', $user->unit_id);
        }
        // Ambil unit sesuai filter
        $units = $unitsQuery->get();

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
                'role'    => \App\Models\Roles::count(),
                'position' => \App\Models\Positions::count(),
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
        // Validasi file yang di-upload
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls',
        ]);

        // Ambil file dari request
        $file = $request->file('file');
        $unit_id = $request->input('unit_id');
        $tahun_ajaran_id = $request->input('tahun_ajaran_id');

        try {
            $file = $request->file('file');
            $filePath = $file->store('temp');
            // Mengantri job untuk diproses di background
            dispatch(new ImportSiswaJob($unit_id, $tahun_ajaran_id, $filePath));
        } catch (\Exception $e) {
            return back()->with('danger', 'Gagal import data siswa: ' . $e->getMessage());
        }

        return back()->with('success', 'Data siswa sedang diproses di background!');
    }



    public function importKelas(Request $request)
    {
        // Validasi file yang di-upload
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls',
        ]);

        $unit_id = $request->input('unit_id');
        $tahun_ajaran_id = $request->input('tahun_ajaran_id');
        try{
            $file = $request->file('file');
            $filePath = $file->store('temp');
            dispatch(new ImportKelasJob($unit_id, $tahun_ajaran_id, $filePath));
        }catch (\Exception $e){
            return back()->with('danger', 'Gagal import data Officer: ' . $e->getMessage());
        }

        return back()->with('success', 'Data kelas sedang diproses di background!');
    }


    public function importOfficer(Request $request)
    {
        // Validasi file yang di-upload
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $unit_id = $request->input('unit_id');
        $tahun_ajaran_id = $request->input('tahun_ajaran_id');

        try{
            $file = $request->file('file');
            $filePath = $file->store('temp');
            dispatch(new ImportOfficerJob($unit_id, $tahun_ajaran_id, $filePath));
        }catch (\Exception $e){
            return back()->with('danger', 'Gagal import data Officer: ' . $e->getMessage());
        }
        return back()->with('success', 'Data officer sedang diproses di background.');
    }



    public function importJurusan(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        $unit_id = $request->input('unit_id');
        $tahun_ajaran_id = $request->input('tahun_ajaran_id');

        try{
            $file = $request->file('file');
            $filePath = $file->store('temp');
            dispatch(new ImportJurusanJob($unit_id, $tahun_ajaran_id, $filePath));
        }catch (\Exception $e){
            return back()->with('danger', 'Gagal import data Jurusan: ' . $e->getMessage());
        }

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

    public function importRole(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        try{
            $file = $request->file('file');
            $filePath = $file->store('temp');
            dispatch(new ImportRoleJob($filePath));
        }catch (\Exception $e){
            return back()->with('danger', 'Gagal import data Role: ' . $e->getMessage());
        }

        return back()->with('success', 'Data role berhasil diimport!');
    }

    public function importPosition(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        try{
            $file = $request->file('file');
            $filePath = $file->store('temp');
            dispatch(new ImportPositionJob($filePath));
        }catch (\Exception $e){
            return back()->with('danger', 'Gagal import data Position: ' . $e->getMessage());
        }

        return back()->with('success', 'Data position (jabatan) berhasil diimport!');
    }

    public function exportRole()
    {
        return Excel::download(new RoleExport(), 'role.xlsx');
    }

    public function exportPosition()
    {
        return Excel::download(new PositionExport(), 'position.xlsx');
    }

}
