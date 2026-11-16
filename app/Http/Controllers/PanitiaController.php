<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CalonSiswa;
use App\Models\Panitia;
use App\Models\Seleksi;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PanitiaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Panitia::with('user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_panitia', 'like', '%' . $search . '%')
                  ->orWhere('no_hp', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function ($sub) use ($search) {
                      $sub->where('nama', 'like', '%' . $search . '%')
                          ->orWhere('email', 'like', '%' . $search . '%')
                          ->orWhere('no_hp', 'like', '%' . $search . '%');
                  });
            });
        }

        $data = $query->get();

        return view('panitia.index', compact('data', 'search'));
    }

    public function add()
    {
        return view('panitia.add');
    }

    public function proses(Request $request)
    {
        // Proses menyimpan data user
        $username = $request->name ?: strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $request->email)[0]));
        $noHp = $request->no_hp ?? '';
        $user = User::create([
            'nama' => $request->nama ?: $request->name,
            'username' => $username,
            'email' => $request->email,
            'no_hp' => $noHp,
            'password' => Hash::make($request->password),
            'role' => 'Panitia',
        ]);

        // Menyimpan data panitia
        $panitia = Panitia::create([
            'id_user' => $user->id_user,
            'nama_panitia' => $request->nama ?: $request->name,
            'no_hp' => $noHp,
            'status' => $request->status ?? 'Aktif',
        ]);

        return redirect()->route('datapanitia')->with('success', 'Data Panitia Berhasil Disimpan');
    }

    public function edit($id)
    {
        $data = Panitia::where('id_panitia', $id)->firstOrFail();
        return view('panitia.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $data = Panitia::with('user')->find($id);
        if (!$data) {
            return redirect()->route('datapanitia')->with('error', 'Data panitia tidak ditemukan.');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nama' => 'required|string|max:255',
            'status' => 'required|in:Aktif,Non Aktif',
        ];

        if ($request->filled('password')) {
            $rules['password'] = $request->filled('password_confirmation') ? 'min:6|confirmed' : 'min:6';
        }

        $messages = [
            'name.required' => 'Username wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.min' => 'Password baru minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'nama.required' => 'Nama lengkap wajib diisi.',
        ];

        $request->validate($rules, $messages);

        if ($data->user) {
            $data->user->nama = $request->nama ?: $request->name;
            $data->user->username = $request->name ?: $data->user->username;
            $data->user->email = $request->email;
            if ($request->has('no_hp')) {
                $data->user->no_hp = $request->no_hp;
            }
            if ($request->filled('password')) {
                $data->user->password = Hash::make($request->password);
            }
            $data->user->save();
        }

        $data->nama_panitia = $request->nama ?: $request->name;
        if ($request->has('no_hp')) {
            $data->no_hp = $request->no_hp;
        }
        $data->status = $request->status ?? 'Aktif';
        $data->save();

        return redirect()->route('datapanitia')->with('success', 'Data panitia dan akun berhasil diperbarui.');
    }

    public function delete(Request $request, $id)
    {
        $panitia = Panitia::find($id);
        if ($panitia) {
            $userId = $panitia->id_user;
            $panitia->delete();
            if ($userId) {
                User::where('id_user', $userId)->delete();
            }
        } else {
            User::where('id_user', $id)->delete();
        }
        return redirect()->route('datapanitia')->with('delete', 'Data Panitia Berhasil Dihapus');
    }

    /**
     * Tampilan data calon siswa untuk evaluasi nilai seleksi
     */
    public function nilai(Request $request)
    {
        $search = $request->input('search');

        // Cari tahun ajar yang sedang berlangsung
        $tahunAjarBerlangsung = TahunAjaran::where('status', 'Berlangsung')->first()
            ?? TahunAjaran::orderBy('id_tahun_ajaran', 'desc')->first();

        $tahunAjarId = $tahunAjarBerlangsung ? $tahunAjarBerlangsung->id_tahun_ajaran : null;

        // Query calon siswa yang layak seleksi dengan eager loading untuk mencegah N+1
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

        // Hitung statistik agregat peserta seleksi
        $totalPeserta = $data->count();
        $sudahDinilai = $data->filter(fn($c) => $c->pembayaran?->seleksi !== null)->count();
        $belumDinilai = $totalPeserta - $sudahDinilai;
        $sudahDiumumkan = $data->filter(fn($c) => $c->pembayaran?->seleksi?->isDiumumkan())->count();

        return view('panitia.datanilai', compact(
            'data',
            'tahunAjarBerlangsung',
            'search',
            'totalPeserta',
            'sudahDinilai',
            'belumDinilai',
            'sudahDiumumkan'
        ));
    }

    /**
     * Form input / edit nilai seleksi calon siswa
     */
    public function input(Request $request, $id)
    {
        $casis = CalonSiswa::with(['pembayaran.seleksi.panitiaPengumum', 'tahunAjaran'])
            ->where('id_calon_siswa', $id)
            ->firstOrFail();

        // Cek kelayakan peserta
        if (!$casis->pembayaran || !in_array($casis->pembayaran->status_pembayaran, ['Lunas', 'Diterima'])) {
            return redirect()->route('nilai')->with('error', 'Calon siswa ini belum menyelesaikan pembayaran pendaftaran.');
        }

        $seleksi = $casis->pembayaran->seleksi;
        $data = $casis; // Kompatibilitas variabel $data di view
        $isLocked = $seleksi ? $seleksi->isDiumumkan() : false;

        return view('panitia.inputnilai', compact('data', 'casis', 'seleksi', 'isLocked'));
    }

    /**
     * Simpan / perbarui nilai seleksi calon siswa (Draft Evaluasi)
     */
    public function simpan(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nilai_baca' => 'required|integer|min:0|max:100',
            'nilai_tulis' => 'required|integer|min:0|max:100',
            'nilai_hitung' => 'required|integer|min:0|max:100',
            'tgl_seleksi' => 'required|date|before_or_equal:today',
        ], [
            'nilai_baca.required' => 'Nilai membaca wajib diisi.',
            'nilai_baca.integer' => 'Nilai membaca harus berupa bilangan bulat.',
            'nilai_baca.min' => 'Nilai membaca minimal 0.',
            'nilai_baca.max' => 'Nilai membaca maksimal 100.',
            'nilai_tulis.required' => 'Nilai menulis wajib diisi.',
            'nilai_tulis.integer' => 'Nilai menulis harus berupa bilangan bulat.',
            'nilai_tulis.min' => 'Nilai menulis minimal 0.',
            'nilai_tulis.max' => 'Nilai menulis maksimal 100.',
            'nilai_hitung.required' => 'Nilai berhitung wajib diisi.',
            'nilai_hitung.integer' => 'Nilai berhitung harus berupa bilangan bulat.',
            'nilai_hitung.min' => 'Nilai berhitung minimal 0.',
            'nilai_hitung.max' => 'Nilai berhitung maksimal 100.',
            'tgl_seleksi.required' => 'Tanggal penilaian seleksi wajib diisi.',
            'tgl_seleksi.date' => 'Format tanggal penilaian tidak valid.',
            'tgl_seleksi.before_or_equal' => 'Tanggal penilaian tidak boleh lebih dari hari ini.',
        ]);

        $casis = CalonSiswa::with('pembayaran.seleksi')->where('id_calon_siswa', $id)->firstOrFail();
        $pembayaran = $casis->pembayaran;

        if (!$pembayaran || !in_array($pembayaran->status_pembayaran, ['Lunas', 'Diterima'])) {
            return redirect()->back()->with('error', 'Calon siswa belum menyelesaikan pembayaran pendaftaran.');
        }

        // Cek apakah nilai seleksi sudah terkunci / diumumkan
        if ($pembayaran->seleksi && $pembayaran->seleksi->isDiumumkan()) {
            return redirect()->route('nilai')->with('error', 'Nilai seleksi telah dipublikasikan dan terkunci. Nilai tidak dapat diubah kembali.');
        }

        // Hitung total dan tentukan hasil seleksi di sisi SERVER (abaikan input klien apapun)
        $nilaiBaca = (int) $validatedData['nilai_baca'];
        $nilaiTulis = (int) $validatedData['nilai_tulis'];
        $nilaiHitung = (int) $validatedData['nilai_hitung'];
        $totalNilai = Seleksi::hitungTotalNilai($nilaiBaca, $nilaiTulis, $nilaiHitung);
        $hasilSeleksi = Seleksi::hitungHasilSeleksi($totalNilai);

        $panitia = Auth::user()?->panitia ?? Panitia::first();
        $idPanitia = $panitia ? $panitia->id_panitia : null;

        Seleksi::updateOrCreate(
            ['id_pembayaran' => $pembayaran->id_pembayaran],
            [
                'id_panitia' => $idPanitia,
                'tgl_seleksi' => $validatedData['tgl_seleksi'],
                'nilai_baca' => $nilaiBaca,
                'nilai_tulis' => $nilaiTulis,
                'nilai_hitung' => $nilaiHitung,
                'total_nilai' => $totalNilai,
                'hasil_seleksi' => $hasilSeleksi,
            ]
        );

        // Catatan: status_pendaftaran siswa TIDAK diubah di sini agar belum bocor sebelum pengumuman resmi dipublikasikan

        return redirect()->route('nilai')->with('success', 'Penilaian seleksi untuk ' . $casis->nama_calon_siswa . ' berhasil disimpan sebagai draft.');
    }

    /**
     * Hapus data nilai seleksi
     */
    public function hapus($id)
    {
        $casis = CalonSiswa::with('pembayaran.seleksi')->where('id_calon_siswa', $id)->firstOrFail();

        if ($casis->pembayaran && $casis->pembayaran->seleksi) {
            if ($casis->pembayaran->seleksi->isDiumumkan()) {
                return redirect()->route('nilai')->with('error', 'Nilai seleksi telah dipublikasikan dan terkunci. Data tidak dapat dihapus.');
            }

            $casis->pembayaran->seleksi->delete();
        }

        return redirect()->route('nilai')->with('success', 'Data penilaian seleksi berhasil dihapus.');
    }
}
