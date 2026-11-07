<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CalonSiswa;
use App\Models\Seleksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengumumanController extends Controller
{
    /**
     * Tampilan pengumuman hasil seleksi untuk calon siswa / orang tua
     */
    public function index()
    {
        $user = auth()->user();
        $casis = $user->calonSiswa ? $user->calonSiswa->load(['tahunAjaran', 'pembayaran.seleksi']) : null;
        $archivedCasis = $casis ? null : $user->calonSiswa()->onlyTrashed()->with(['tahunAjaran', 'pembayaran.seleksi'])->first();
        $isArchived = !empty($archivedCasis);

        if ($casis && $casis->pembayaran && $casis->pembayaran->seleksi) {
            $seleksi = $casis->pembayaran->seleksi;
            if ($seleksi->isDiumumkan() && $seleksi->hasil_seleksi !== null) {
                $data = collect([$seleksi]);
            } else {
                $data = collect();
            }
        } else {
            $data = collect();
        }

        $pengumumanTersedia = $data->isNotEmpty();
        $tahunajar = $casis?->tahunAjaran ?? $archivedCasis?->tahunAjaran ?? \App\Models\TahunAjaran::where('status', 'Berlangsung')->first();

        return view('pengumuman.index', compact('data', 'pengumumanTersedia', 'casis', 'archivedCasis', 'isArchived', 'tahunajar'));
    }

    /**
     * Unduh surat keterangan hasil seleksi dalam bentuk PDF
     */
    public function unduh($id)
    {
        $casis = CalonSiswa::with(['pembayaran.seleksi', 'tahunAjaran'])->where('id_calon_siswa', $id)->firstOrFail();
        $user = auth()->user();

        // Otorisasi: Casis/Orang Tua hanya bisa mengunduh miliknya sendiri
        if ($user && in_array($user->role, ['Orang Tua', 'Calon Siswa']) && (int)$casis->id_user !== (int)($user->id_user ?? $user->getKey())) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $seleksi = $casis->pembayaran ? $casis->pembayaran->seleksi : null;

        if (!$seleksi || !$seleksi->isDiumumkan() || !$seleksi->hasil_seleksi) {
            return redirect()->back()->with('error', 'Pengumuman hasil seleksi belum resmi dipublikasikan.');
        }

        $aktivTahunAjaran = $casis->tahunAjaran;
        $data = $seleksi;

        $pdf = Pdf::loadView('pengumuman.unduh', compact('data', 'casis', 'aktivTahunAjaran'));

        return $pdf->download('pengumuman_seleksi_' . $casis->id_calon_siswa . '.pdf');
    }
}
