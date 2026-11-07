<?php

namespace App\Http\Controllers;

use App\Models\CalonSiswa;
use App\Models\Dokumen;
use App\Models\LogVerifikasiDokumen;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\VerifikasiDokumenService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PendaftaranController extends Controller
{
    protected VerifikasiDokumenService $verifikasiService;

    public function __construct(VerifikasiDokumenService $verifikasiService)
    {
        $this->verifikasiService = $verifikasiService;
    }

    /**
     * Halaman antrean Verifikasi Dokumen calon siswa untuk Admin.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $tab = $request->input('tab', 'menunggu');
        if (!in_array($tab, ['menunggu', 'perlu_perbaikan', 'terverifikasi', 'semua'])) {
            $tab = 'menunggu';
        }

        // Daftar Tahun Ajaran untuk Filter
        $tahunAjaranList = TahunAjaran::orderBy('id_tahun_ajaran', 'desc')->get();
        $validTaIds = $tahunAjaranList->pluck('id_tahun_ajaran')->toArray();
        $rawTa = $request->input('ta');
        $filterTa = ($rawTa !== null && in_array((int)$rawTa, $validTaIds, true)) ? (int)$rawTa : null;

        // Hitung ringkasan angka untuk tab antrean (query efisien tanpa N+1)
        $summary = (object) [
            'menunggu' => CalonSiswa::whereNull('deleted_at')
                ->whereHas('dokumen', function ($d) {
                    $d->where('status_verifikasi', 'Menunggu');
                })->count(),
            'perlu_perbaikan' => CalonSiswa::whereNull('deleted_at')
                ->where('status_pendaftaran', 'Ditolak')
                ->count(),
            'terverifikasi' => CalonSiswa::whereNull('deleted_at')
                ->where('status_pendaftaran', 'Diterima')
                ->count(),
            'semua' => CalonSiswa::whereNull('deleted_at')->count(),
        ];

        $query = CalonSiswa::with(['user', 'dokumen', 'tahunAjaran'])
            ->whereNull('deleted_at');

        // Filter Tahun Ajaran jika dipilih
        if ($filterTa) {
            $query->where('id_tahun_ajaran', $filterTa);
        }

        // Filter pencarian berdasarkan nama casis atau no registrasi
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_calon_siswa', 'like', "%{$q}%")
                    ->orWhere('no_registrasi', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('nama', 'like', "%{$q}%");
                    });
            });
        }

        // Filter berdasarkan tab antrean
        match ($tab) {
            'menunggu' => $query->whereHas('dokumen', function ($d) {
                $d->where('status_verifikasi', 'Menunggu');
            }),
            'perlu_perbaikan' => $query->where('status_pendaftaran', 'Ditolak'),
            'terverifikasi' => $query->where('status_pendaftaran', 'Diterima'),
            'semua' => $query,
        };

        // Urutan default antrean: calon siswa dengan dokumen Menunggu berada di urutan atas,
        // lalu diurutkan berdasarkan pendaftaran terlama (antrean adil)
        $data = $query->orderByRaw("
            CASE 
                WHEN EXISTS (SELECT 1 FROM tb_dokumen WHERE tb_dokumen.id_calon_siswa = tb_calon_siswa.id_calon_siswa AND tb_dokumen.status_verifikasi = 'Menunggu') THEN 0 
                ELSE 1 
            END ASC
        ")
        ->orderBy('tgl_pendaftaran', 'asc')
        ->orderBy('id_calon_siswa', 'asc')
        ->paginate(15)
        ->withQueryString();

        return view('pendaftaran.index', compact('data', 'summary', 'tab', 'q', 'tahunAjaranList', 'filterTa'));
    }

    /**
     * Halaman detail periksa dokumen persyaratan per calon siswa.
     */
    public function periksa($id)
    {
        $casis = CalonSiswa::with(['user', 'dokumen', 'tahunAjaran'])
            ->whereNull('deleted_at')
            ->findOrFail($id);

        $logs = LogVerifikasiDokumen::with('admin')
            ->where('id_calon_siswa', $casis->id_calon_siswa)
            ->orderBy('created_at', 'desc')
            ->get();

        // Dokumen yang wajib dan opsional
        $docMap = [
            'Akta Kelahiran' => $casis->dokumen->firstWhere('jenis_dokumen', 'Akta Kelahiran'),
            'Kartu Keluarga' => $casis->dokumen->firstWhere('jenis_dokumen', 'Kartu Keluarga'),
            'Pas Foto'       => $casis->dokumen->firstWhere('jenis_dokumen', 'Pas Foto'),
            'KPS'            => $casis->dokumen->firstWhere('jenis_dokumen', 'KPS'),
        ];

        return view('pendaftaran.periksa', compact('casis', 'logs', 'docMap'));
    }

    /**
     * Aksi verifikasi satu item dokumen (Tandai Valid / Tolak dengan Catatan).
     */
    public function verifikasiItem(Request $request, $id_dokumen)
    {
        if (Auth::user()?->role !== 'Admin') {
            abort(403, 'Hanya Admin yang berhak memverifikasi dokumen pendaftaran.');
        }

        $validated = $request->validate([
            'status'  => 'required|in:Valid,Tidak Valid',
            'catatan' => 'required_if:status,Tidak Valid|nullable|string|max:500',
        ], [
            'status.required' => 'Status verifikasi harus dipilih.',
            'status.in'       => 'Status verifikasi tidak valid.',
            'catatan.required_if' => 'Alasan penolakan dokumen wajib diisi jika dokumen ditolak.',
        ]);

        $dokumen = Dokumen::findOrFail($id_dokumen);

        $result = $this->verifikasiService->verifikasiDokumen(
            $dokumen,
            $validated['status'],
            $validated['catatan'] ?? null,
            Auth::user()
        );

        $pesan = $validated['status'] === 'Valid'
            ? "Dokumen {$dokumen->jenis_dokumen} berhasil diverifikasi sebagai Valid."
            : "Dokumen {$dokumen->jenis_dokumen} ditolak dan status pendaftaran disesuaikan menjadi Perlu Perbaikan.";

        return redirect()->back()->with('success', $pesan);
    }

    /**
     * Aksi override manual status pendaftaran oleh Admin dengan alasan wajib.
     */
    public function overrideStatus(Request $request, $id)
    {
        if (Auth::user()?->role !== 'Admin') {
            abort(403, 'Hanya Admin yang berhak mengubah status pendaftaran secara manual.');
        }

        $validated = $request->validate([
            'status_pendaftaran' => 'required|in:Menunggu,Diterima,Ditolak',
            'alasan'             => 'required|string|min:5|max:500',
        ], [
            'status_pendaftaran.required' => 'Pilih status pendaftaran tujuan.',
            'alasan.required'             => 'Alasan override status pendaftaran wajib diisi.',
            'alasan.min'                  => 'Alasan override minimal 5 karakter.',
        ]);

        $casis = CalonSiswa::findOrFail($id);

        $this->verifikasiService->overrideStatusPendaftaran(
            $casis,
            $validated['status_pendaftaran'],
            $validated['alasan'],
            Auth::user()
        );

        return redirect()->back()->with('success', "Status pendaftaran {$casis->nama_calon_siswa} berhasil di-override menjadi {$validated['status_pendaftaran']}.");
    }

    /**
     * Backward compatibility untuk route tampil & proses-data lama
     */
    public function tampil(Request $request, $id)
    {
        return $this->periksa($id);
    }

    public function prosesdata(Request $request, $id)
    {
        return $this->overrideStatus($request, $id);
    }

    public function formpendaftaran()
    {
        return view('pendaftaran.form');
    }

    public function proses(Request $request)
    {
        $tahunAjarBerlangsung = TahunAjaran::where('status', 'Berlangsung')->first();

        if ($tahunAjarBerlangsung) {
            $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $request->email)[0]));
            $username = $baseUsername ?: 'user_' . time();
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter++;
            }

            $user = User::create([
                'nama' => $request->name ?: $request->nama,
                'username' => $username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'Orang Tua',
            ]);

            $casis = CalonSiswa::create([
                'id_user'            => $user->id_user,
                'id_tahun_ajaran'    => $tahunAjarBerlangsung->id_tahun_ajaran,
                'nik_calon_siswa'    => $request->nik,
                'nama_calon_siswa'   => $request->nama,
                'jenis_kelamin'      => $request->jenis_kelamin,
                'tmpt_lahir'         => $request->tempat_lahir,
                'tgl_lahir'          => $request->tanggal_lahir,
                'nama_ayah'          => $request->nama_ortu,
                'alamat_calon_siswa' => $request->alamat,
                'no_hp_orang_tua'    => $request->no_hp,
                'tgl_pendaftaran'    => Carbon::now(),
                'status_pendaftaran' => 'Menunggu',
            ]);

            return redirect()->route('datapendaftaran')->with('success', 'Data calon siswa baru berhasil ditambahkan.');
        }

        return redirect()->back()->with('error', 'Tidak ada tahun ajaran yang sedang aktif.');
    }
}
