<?php

namespace App\Http\Controllers;

use App\Models\foto;
use App\Models\User;
use App\Models\TahunAjaran;
use App\Models\CalonSiswa;
use App\Models\Seleksi;
use App\Models\Panitia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\HasilSeleksiNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class PengaturanController extends Controller
{
    public function index()
    {
        $foto = foto::where('status', 'Mulai')->first();
        return view('welcome', compact('foto'));
    }

    public function pengaturan()
    {
        $latestPhoto = foto::where('status', 'Mulai')->latest()->first();
        return view('pengaturan.landingpage', compact('latestPhoto'));
    }

    public function upload(Request $request)
    {
        try {
            // Validasi file
            $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:3048',
            ]);

            $imageName = time() . '.' . $request->photo->extension();

            DB::beginTransaction();

            // Upload file ke storage
            if (!$request->photo->storeAs('foto', $imageName, 'public')) {
                throw new \Exception('Gagal mengunggah file.');
            }

            // Mengubah status semua foto lama menjadi 'Berakhir'
            foto::where('status', 'Mulai')->update(['status' => 'Berakhir']);

            // Simpan data foto baru ke dalam database
            $foto = new foto;
            $foto->foto = $imageName;
            $foto->status = 'Mulai';
            $foto->save();

            DB::commit();

            return redirect()->back()->with('success', 'Foto berhasil diunggah! File disimpan di storage/app/public/foto/' . $imageName);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log error
            \Log::error('Error uploading foto: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunggah foto. Silakan periksa apakah file memenuhi persyaratan dan coba lagi.');
        }
    }

    public function datauser(Request $request)
    {
        $search = trim($request->input('search', ''));
        $role = trim($request->input('role', ''));

        $query = User::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($role !== '') {
            $query->where('role', $role);
        }

        $data = $query->orderByRaw("CASE 
            WHEN role = 'Admin' THEN 1 
            WHEN role = 'Panitia' THEN 2 
            ELSE 3 END")
            ->orderBy('nama', 'asc')
            ->get();

        $totalUsers = User::count();
        $totalAdmin = User::where('role', 'Admin')->count();
        $totalPanitia = User::where('role', 'Panitia')->count();
        $totalOrtu = User::where('role', 'Orang Tua')->count();

        return view('pengaturan.datauser', compact(
            'data',
            'search',
            'role',
            'totalUsers',
            'totalAdmin',
            'totalPanitia',
            'totalOrtu'
        ));
    }

    public function resetPasswordUser(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = User::findOrFail($id);
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('beranda.datauser')->with('success', "Password untuk akun {$user->nama} ({$user->email}) berhasil diubah.");
    }

    public function tahun()
    {
        $data = TahunAjaran::all();

        return view('pengaturan.tahun', compact('data'));
    }

    public function add()
    {
        return view('pengaturan.add');
    }

    public function simpan(Request $request)
    {
        $biayaPembangunan = (float) $request->input('biaya_pembangunan', 0);
        $biayaBuku = (float) $request->input('biaya_buku', 0);
        $biayaSeragam = (float) $request->input('biaya_seragam', 0);

        $totalBiayaMasuk = $biayaPembangunan + $biayaBuku + $biayaSeragam;
        if ($totalBiayaMasuk <= 0 && $request->filled('biaya_daftar_ulang')) {
            $totalBiayaMasuk = (float) $request->input('biaya_daftar_ulang');
        }

        $tahunajar = TahunAjaran::create([
            'tahun_ajaran' => $request->tahun_ajar ?? $request->tahun_ajaran,
            'mulai_pendaftaran' => $request->mulai_pendaftaran,
            'batas_pendaftaran' => $request->batas_pendaftaran,
            'status' => $request->status,
            'tgl_seleksi' => $request->tgl_seleksi,
            'kuota' => $request->kuota,
            'biaya_pendaftaran' => $request->biaya_pendaftaran ?? 150000,
            'biaya_pembangunan' => $biayaPembangunan,
            'biaya_buku' => $biayaBuku,
            'biaya_seragam' => $biayaSeragam,
            'biaya_daftar_ulang' => $totalBiayaMasuk > 0 ? $totalBiayaMasuk : 1000000,
        ]);

        return redirect()->route('beranda.tahun')->with('success', 'Data Berhasil Disimpan');
    }

    public function edit($id)
    {
        $data = TahunAjaran::where('id_tahun_ajaran', $id)->firstOrFail();
        return view('pengaturan.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $data = [
            'tahun_ajaran' => $request->input('tahun_ajar') ?? $request->input('tahun_ajaran'),
            'mulai_pendaftaran' => $request->input('mulai_pendaftaran'),
            'batas_pendaftaran' => $request->input('batas_pendaftaran'),
            'status' => $request->input('status'),
            'tgl_seleksi' => $request->input('tgl_seleksi'),
            'kuota' => $request->input('kuota'),
        ];

        if ($request->filled('biaya_pendaftaran')) {
            $data['biaya_pendaftaran'] = $request->input('biaya_pendaftaran');
        }

        $biayaPembangunan = $request->input('biaya_pembangunan');
        $biayaBuku = $request->input('biaya_buku');
        $biayaSeragam = $request->input('biaya_seragam');

        if ($biayaPembangunan !== null) {
            $data['biaya_pembangunan'] = (float) $biayaPembangunan;
        }
        if ($biayaBuku !== null) {
            $data['biaya_buku'] = (float) $biayaBuku;
        }
        if ($biayaSeragam !== null) {
            $data['biaya_seragam'] = (float) $biayaSeragam;
        }

        if ($biayaPembangunan !== null || $biayaBuku !== null || $biayaSeragam !== null) {
            $data['biaya_daftar_ulang'] = (float) ($data['biaya_pembangunan'] ?? 0)
                                        + (float) ($data['biaya_buku'] ?? 0)
                                        + (float) ($data['biaya_seragam'] ?? 0);
        } elseif ($request->filled('biaya_daftar_ulang')) {
            $data['biaya_daftar_ulang'] = (float) $request->input('biaya_daftar_ulang');
        }

        TahunAjaran::where('id_tahun_ajaran', $id)->update($data);

        return redirect()->route('beranda.tahun')->with('success', 'Data Berhasil Diperbarui');
    }

    public function delete(Request $request, $id)
    {
        TahunAjaran::where('id_tahun_ajaran', $id)->delete();
        return redirect()->route('beranda.tahun')->with('success', 'Data Berhasil Dihapus');
    }

    public function pengseleksi(Request $request)
    {
        $search = $request->input('search');

        $tahunAjarBerlangsung = TahunAjaran::where('status', 'Berlangsung')->first()
            ?? TahunAjaran::orderBy('id_tahun_ajaran', 'desc')->first();

        $tahunAjarId = $tahunAjarBerlangsung ? $tahunAjarBerlangsung->id_tahun_ajaran : null;

        $query = CalonSiswa::layakSeleksi($tahunAjarId)
            ->with([
                'pembayaran.seleksi.panitia',
                'pembayaran.seleksi.panitiaPengumum',
                'tahunAjaran',
            ]);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_calon_siswa', 'like', '%' . $search . '%')
                  ->orWhere('nik_calon_siswa', 'like', '%' . $search . '%')
                  ->orWhere('no_registrasi', 'like', '%' . $search . '%');
            });
        }

        $data = $query->orderBy('nama_calon_siswa', 'asc')->get();
        $aktivTahunAjaran = $tahunAjarBerlangsung;

        // Statistik peserta seleksi
        $totalPeserta = $data->count();
        $sudahDinilai = $data->filter(fn($c) => $c->pembayaran?->seleksi !== null)->count();
        $belumDinilai = $totalPeserta - $sudahDinilai;
        $jumlahLulus = $data->filter(fn($c) => $c->pembayaran?->seleksi?->hasil_seleksi === 'Lulus')->count();
        $jumlahTidakLulus = $data->filter(fn($c) => $c->pembayaran?->seleksi?->hasil_seleksi === 'Tidak Lulus')->count();

        // Status publikasi
        $firstDiumumkan = $data->first(fn($c) => $c->pembayaran?->seleksi?->isDiumumkan())?->pembayaran?->seleksi;
        $isDiumumkan = !is_null($firstDiumumkan);
        $waktuPublikasi = $firstDiumumkan?->diumumkan_pada;
        $panitiaPengumum = $firstDiumumkan?->panitiaPengumum;

        $isSemuaDinilai = ($totalPeserta > 0) && ($belumDinilai === 0);

        $user = auth()->user();
        if ($user && $user->role === 'Panitia') {
            return view('panitia.pengumuman_seleksi', compact(
                'data',
                'aktivTahunAjaran',
                'search',
                'totalPeserta',
                'sudahDinilai',
                'belumDinilai',
                'jumlahLulus',
                'jumlahTidakLulus',
                'isDiumumkan',
                'waktuPublikasi',
                'panitiaPengumum',
                'isSemuaDinilai'
            ));
        }

        return view('pengaturan.pengumuman', compact('data', 'aktivTahunAjaran', 'search'));
    }

    /**
     * Publikasikan hasil seleksi secara serentak dan kunci nilai
     */
    public function publikasikanHasil(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'Panitia') {
            abort(403, 'Akses publikasi pengumuman seleksi hanya diizinkan untuk Panitia.');
        }

        $tahunAjarBerlangsung = TahunAjaran::where('status', 'Berlangsung')->first()
            ?? TahunAjaran::orderBy('id_tahun_ajaran', 'desc')->first();

        $tahunAjarId = $tahunAjarBerlangsung ? $tahunAjarBerlangsung->id_tahun_ajaran : null;

        return DB::transaction(function () use ($tahunAjarId, $user) {
            $casisList = CalonSiswa::layakSeleksi($tahunAjarId)
                ->with(['pembayaran.seleksi'])
                ->get();

            $totalPeserta = $casisList->count();
            if ($totalPeserta === 0) {
                return redirect()->back()->with('error', 'Tidak ada calon siswa yang layak mengikuti seleksi pada periode tahun ajaran ini.');
            }

            // Validasi kelengkapan: Semua peserta layak seleksi harus sudah dinilai
            $belumDinilai = $casisList->filter(fn($c) => $c->pembayaran?->seleksi === null);
            if ($belumDinilai->count() > 0) {
                return redirect()->back()->with('error', 'Publikasi gagal: Masih ada ' . $belumDinilai->count() . ' peserta seleksi yang belum dinilai. Silakan lengkapi seluruh penilaian sebelum mempublikasikan hasil.');
            }

            // Cek idempoten: apakah seluruhnya sudah dipublikasikan sebelumnya
            $sudahDipublikasikan = $casisList->every(fn($c) => $c->pembayaran?->seleksi?->isDiumumkan());
            if ($sudahDipublikasikan) {
                return redirect()->route('panitia.pengumuman.seleksi')->with('info', 'Hasil seleksi untuk tahun ajaran ini sudah dipublikasikan sebelumnya.');
            }

            $panitia = $user->panitia ?? Panitia::where('id_user', $user->id_user ?? $user->getKey())->first();
            $idPanitia = $panitia ? $panitia->id_panitia : null;
            $now = now();

            $pembayaranIds = $casisList->pluck('pembayaran.id_pembayaran')->filter();

            // 1. Kunci nilai dan catat waktu serta petugas publikasi
            Seleksi::whereIn('id_pembayaran', $pembayaranIds)
                ->whereNull('diumumkan_pada')
                ->update([
                    'diumumkan_pada' => $now,
                    'diumumkan_oleh' => $idPanitia,
                ]);

            // 2. Sinkronkan status pendaftaran calon siswa secara resmi
            $lulusPembayaranIds = Seleksi::whereIn('id_pembayaran', $pembayaranIds)
                ->where('hasil_seleksi', 'Lulus')
                ->pluck('id_pembayaran');

            CalonSiswa::whereHas('pembayaran', fn($q) => $q->whereIn('id_pembayaran', $lulusPembayaranIds))
                ->update(['status_pendaftaran' => 'Diterima']);

            $gagalPembayaranIds = Seleksi::whereIn('id_pembayaran', $pembayaranIds)
                ->where('hasil_seleksi', 'Tidak Lulus')
                ->pluck('id_pembayaran');

            CalonSiswa::whereHas('pembayaran', fn($q) => $q->whereIn('id_pembayaran', $gagalPembayaranIds))
                ->update(['status_pendaftaran' => 'Ditolak']);

            // Catat log terstruktur tanpa data pribadi/sensitif
            Log::info('Publikasi hasil seleksi PPDB berhasil', [
                'id_panitia' => $idPanitia,
                'id_tahun_ajaran' => $tahunAjarId,
                'total_peserta' => $totalPeserta,
                'total_lulus' => $lulusPembayaranIds->count(),
                'total_tidak_lulus' => $gagalPembayaranIds->count(),
                'waktu_publikasi' => $now->toDateTimeString(),
            ]);

            return redirect()->route('panitia.pengumuman.seleksi')->with('success', 'Hasil seleksi calon siswa berhasil dipublikasikan dan resmi diumumkan ke seluruh calon siswa!');
        });
    }

    /**
     * Fallback / Kompatibilitas route lama
     */
    public function updateStatusSeleksi(Request $request)
    {
        return $this->publikasikanHasil($request);
    }
}
