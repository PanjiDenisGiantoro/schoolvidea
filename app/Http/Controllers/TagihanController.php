<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Unit;
use Illuminate\Http\Request;

class TagihanController extends Controller
{
    public function create()
    {
        $units = Unit::all();
        $kelas = Kelas::all();
        return view('pages.tagihan.create', compact('units','kelas'));
    }
    public function index()
    {

        return view('pages.tagihan.index');
    }
}
