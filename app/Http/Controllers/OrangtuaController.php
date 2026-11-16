<?php

namespace App\Http\Controllers;

use App\Models\CalonSiswa;
use Illuminate\Http\Request;

class OrangtuaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = CalonSiswa::with('user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_ayah', 'like', '%' . $search . '%')
                  ->orWhere('nama_ibu', 'like', '%' . $search . '%')
                  ->orWhere('nama_calon_siswa', 'like', '%' . $search . '%');
            });
        }

        $data = $query->get();

        return view('ortu.index', compact('data', 'search'));
    }
}
