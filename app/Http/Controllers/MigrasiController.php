<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Officer;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MigrasiController extends Controller
{
    public function index()
    {
        $units = \App\Models\Unit::where('status', '1')->get();

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
        return view('pages.migrasi.migrasi',compact('totals'));
    }
    public function downloadTemplate($type)
    {
        $filePath = "templates/{$type}_template.xlsx";

        if (Storage::disk('local')->exists($filePath)) {
            return Storage::download($filePath);
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
        // logic import kelas
        return back()->with('success', 'Data kelas berhasil diimport!');
    }

    public function importOfficer(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);
        // logic import officer
        return back()->with('success', 'Data officer berhasil diimport!');
    }

    public function importJurusan(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);
        // logic import jurusan
        return back()->with('success', 'Data jurusan berhasil diimport!');
    }
}
