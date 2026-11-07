<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BiayaDaftarUlangController extends Controller
{
    public function index()
    {
        return redirect()->route('beranda.tahun')->with('info', 'Pengaturan biaya daftar ulang telah disatukan pada menu Pengaturan Tahun Ajaran.');
    }

    public function store(Request $request)
    {
        return redirect()->route('beranda.tahun');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('beranda.tahun');
    }

    public function destroy($id)
    {
        return redirect()->route('beranda.tahun');
    }
}
