<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CalonSiswa;
use App\Models\Dokumen;
use App\Models\LogVerifikasiDokumen;
use App\Models\TahunAjaran;
use App\Services\DokumenSecurityValidator;
use App\Services\VerifikasiDokumenService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CasisController extends Controller
{
    public function index(Request $request)
    {
        // 1. Parameter Pencarian (?q= dengan fallback ?search=)
        $q = trim($request->input('q', $request->input('search', '')));
        $escapedQ = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);

        // 2. Daftar Tahun Ajaran untuk Filter & Whitelist
        $tahunAjaranList = TahunAjaran::orderBy('id_tahun_ajaran', 'desc')->get();
        $validTaIds = $tahunAjaranList->pluck('id_tahun_ajaran')->toArray();

        // 3. Validasi & Sanitasi Parameter Filter
        $rawTa = $request->input('ta');
        $filterTa = ($rawTa !== null && in_array((int)$rawTa, $validTaIds, true)) ? (int)$rawTa : null;

        $rawStatusDaftar = $request->input('status_pendaftaran');
        $validStatusDaftar = ['Menunggu', 'Diterima', 'Ditolak'];
        $filterStatusDaftar = in_array($rawStatusDaftar, $validStatusDaftar, true) ? $rawStatusDaftar : null;

        $rawStatusBayar = $request->input('status_pembayaran');
        $validStatusBayar = ['Lunas', 'Menunggu', 'Belum Bayar', 'Gagal'];
        $filterStatusBayar = in_array($rawStatusBayar, $validStatusBayar, true) ? $rawStatusBayar : null;

        $rawStatusArsip = $request->input('status_arsip', 'aktif');
        $validStatusArsip = ['aktif', 'terarsip', 'semua'];
        $filterStatusArsip = in_array($rawStatusArsip, $validStatusArsip, true) ? $rawStatusArsip : 'aktif';

        // 4. Validasi & Sanitasi Sorting
        $validSortBy = ['tgl_pendaftaran', 'nama_calon_siswa', 'no_registrasi', 'status_pendaftaran'];
        $sortBy = in_array($request->input('sort_by'), $validSortBy, true) ? $request->input('sort_by') : 'tgl_pendaftaran';
        $sortDir = strtolower($request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        // 5. Validasi Pagination
        $validPerPage = [10, 25, 50];
        $perPage = in_array((int)$request->input('per_page'), $validPerPage, true) ? (int)$request->input('per_page') : 10;

        // 6. Bangun Query Utama
        $query = CalonSiswa::with(['user', 'dokumen', 'tahunAjaran', 'pembayaran']);

        // Scope Arsip (Soft Delete)
        if ($filterStatusArsip === 'terarsip') {
            $query->onlyTrashed();
        } elseif ($filterStatusArsip === 'semua') {
            $query->withTrashed();
        } // 'aktif' adalah default (whereNull deleted_at)

        // Filter Tahun Ajaran
        if ($filterTa !== null) {
            $query->where('id_tahun_ajaran', $filterTa);
        }

        // Filter Status Pendaftaran
        if ($filterStatusDaftar !== null) {
            $query->where('status_pendaftaran', $filterStatusDaftar);
        }

        // Filter Status Pembayaran
        if ($filterStatusBayar !== null) {
            match ($filterStatusBayar) {
                'Lunas' => $query->whereHas('pembayaran', fn($p) => $p->whereIn('status_pembayaran', ['Lunas', 'Diterima'])),
                'Menunggu' => $query->whereHas('pembayaran', fn($p) => $p->whereIn('status_pembayaran', ['Menunggu', 'Pending'])),
                'Belum Bayar' => $query->doesntHave('pembayaran'),
                'Gagal' => $query->whereHas('pembayaran', fn($p) => $p->whereIn('status_pembayaran', ['Gagal', 'Kadaluarsa', 'Ditolak'])),
            };
        }

        // Pencarian Teks (?q=)
        if ($q !== '') {
            $query->where(function ($sub) use ($escapedQ) {
                // Pencocokan Nama: LIKE '%kata%'
                $sub->where('nama_calon_siswa', 'like', "%{$escapedQ}%")
                    ->orWhere('nama_ayah', 'like', "%{$escapedQ}%")
                    ->orWhere('nama_ibu', 'like', "%{$escapedQ}%")
                    // Pencocokan NIK & No Registrasi: awalan / persis agar memakai index
                    ->orWhere('nik_calon_siswa', 'like', "{$escapedQ}%")
                    ->orWhere('no_registrasi', 'like', "{$escapedQ}%");
            });
        }

        // Eksekusi Sorting & Pagination
        $data = $query->orderBy($sortBy, $sortDir)
            ->orderBy('id_calon_siswa', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // 7. Ringkasan Angka dengan SATU Query Agregat
        $summaryQuery = DB::table('tb_calon_siswa as cs')
            ->leftJoin('tb_pembayaran as p', 'cs.id_calon_siswa', '=', 'p.id_calon_siswa')
            ->when($filterTa, fn($sq) => $sq->where('cs.id_tahun_ajaran', $filterTa));

        $summaryRow = (clone $summaryQuery)
            ->whereNull('cs.deleted_at')
            ->selectRaw("
                COUNT(DISTINCT cs.id_calon_siswa) as total_pendaftar,
                COUNT(DISTINCT CASE WHEN cs.status_pendaftaran = 'Menunggu' THEN cs.id_calon_siswa END) as menunggu_verifikasi,
                COUNT(DISTINCT CASE WHEN cs.status_pendaftaran = 'Diterima' THEN cs.id_calon_siswa END) as terverifikasi,
                COUNT(DISTINCT CASE WHEN cs.status_pendaftaran = 'Ditolak' THEN cs.id_calon_siswa END) as perlu_perbaikan,
                COUNT(DISTINCT CASE WHEN p.id_pembayaran IS NULL THEN cs.id_calon_siswa END) as belum_bayar,
                COUNT(DISTINCT CASE WHEN p.status_pembayaran IN ('Lunas', 'Diterima') THEN cs.id_calon_siswa END) as bayar_lunas,
                COUNT(DISTINCT CASE WHEN p.status_pembayaran IN ('Menunggu', 'Pending') THEN cs.id_calon_siswa END) as menunggu_bayar,
                COUNT(DISTINCT CASE WHEN p.status_pembayaran IN ('Gagal', 'Kadaluarsa', 'Ditolak') THEN cs.id_calon_siswa END) as bayar_gagal
            ")
            ->first();

        $totalTerarsip = DB::table('tb_calon_siswa')
            ->whereNotNull('deleted_at')
            ->when($filterTa, fn($sq) => $sq->where('id_tahun_ajaran', $filterTa))
            ->count();

        $summary = (object)[
            'total' => (int)($summaryRow->total_pendaftar ?? 0),
            'menunggu' => (int)($summaryRow->menunggu_verifikasi ?? 0),
            'terverifikasi' => (int)($summaryRow->terverifikasi ?? 0),
            'perlu_perbaikan' => (int)($summaryRow->perlu_perbaikan ?? 0),
            'belum_bayar' => (int)($summaryRow->belum_bayar ?? 0),
            'lunas' => (int)($summaryRow->bayar_lunas ?? 0),
            'menunggu_bayar' => (int)($summaryRow->menunggu_bayar ?? 0),
            'bayar_gagal' => (int)($summaryRow->bayar_gagal ?? 0),
            'terarsip' => $totalTerarsip,
        ];

        return view('casis.index', compact(
            'data',
            'q',
            'tahunAjaranList',
            'filterTa',
            'filterStatusDaftar',
            'filterStatusBayar',
            'filterStatusArsip',
            'sortBy',
            'sortDir',
            'perPage',
            'summary'
        ));
    }

    public function detail($id)
    {
        $casis = CalonSiswa::with(['user', 'dokumen', 'tahunAjaran', 'pembayaran'])
            ->where('id_calon_siswa', $id)
            ->firstOrFail();

        return view('casis.detail', compact('casis'));
    }

    public function edit($id)
    {
        $casis = CalonSiswa::with(['user', 'dokumen'])
            ->where('id_calon_siswa', $id)
            ->firstOrFail();

        return view('casis.edit', compact('casis'));
    }

    public function update(Request $request, $id)
    {
        $casis = CalonSiswa::where('id_calon_siswa', $id)->first();
        if (!$casis) {
            return redirect()->route('indexcasis')->with('error', 'Data tidak ditemukan.');
        }

        // Update data calon siswa
        $casis->nik_calon_siswa = $request->input('nik', $request->input('nik_calon_siswa', $casis->nik_calon_siswa));
        $casis->nama_calon_siswa = $request->input('nama', $request->input('nama_calon_siswa', $casis->nama_calon_siswa));
        $casis->tmpt_lahir = $request->input('tempat_lahir', $request->input('tmpt_lahir', $casis->tmpt_lahir));
        $casis->tgl_lahir = $request->input('tanggal_lahir', $request->input('tgl_lahir', $casis->tgl_lahir));
        $casis->jenis_kelamin = $request->input('jenis_kelamin', $casis->jenis_kelamin);
        $casis->jmlh_saudara = $request->input('jml_saudara', $request->input('jmlh_saudara', $casis->jmlh_saudara));
        $casis->alamat_calon_siswa = $request->input('alamat', $request->input('alamat_calon_siswa', $casis->alamat_calon_siswa));
        $casis->agama = $request->input('agama', $casis->agama);
        $casis->status_anak = $request->input('status_anak', $casis->status_anak);

        // Data Ayah
        $casis->nama_ayah = $request->input('nama_ayah', $casis->nama_ayah ?: $request->input('nama_ortu'));
        $casis->pendidikan_ayah = $request->input('pendidikan_ayah', $casis->pendidikan_ayah ?: $request->input('pendidikan_ortu'));
        $casis->pekerjaan_ayah = $request->input('pekerjaan_ayah', $casis->pekerjaan_ayah ?: $request->input('pekerjaan_ortu'));
        $casis->penghasilan_ayah = $request->input('penghasilan_ayah', $casis->penghasilan_ayah ?: $request->input('gaji_ortu'));

        // Data Ibu
        $casis->nama_ibu = $request->input('nama_ibu', $casis->nama_ibu);
        $casis->pendidikan_ibu = $request->input('pendidikan_ibu', $casis->pendidikan_ibu);
        $casis->pekerjaan_ibu = $request->input('pekerjaan_ibu', $casis->pekerjaan_ibu);
        $casis->penghasilan_ibu = $request->input('penghasilan_ibu', $casis->penghasilan_ibu);

        // No HP
        $casis->no_hp_orang_tua = $request->input('no_hp_orang_tua', $request->input('no_hp', $casis->no_hp_orang_tua));
        $casis->no_hp_calon_siswa = $request->input('no_hp_calon_siswa', $casis->no_hp_calon_siswa);

        $casis->save();

        // Proses unggah file jika ada pada mode edit
        $documentMap = [
            'akte' => 'Akta Kelahiran',
            'kk'   => 'Kartu Keluarga',
            'foto' => 'Pas Foto',
            'kps'  => 'KPS',
        ];

        foreach ($documentMap as $inputKey => $docType) {
            if ($request->hasFile($inputKey)) {
                $file = $request->file($inputKey);

                // Validasi format ketat per jenis dokumen
                DokumenSecurityValidator::validate($file, $inputKey);

                // Simpan ke storage privat dengan nama acak UUID tanpa timestamp dan tanpa ID
                $ext = strtolower($file->getClientOriginalExtension());
                $randomFilename = Str::uuid()->toString() . '.' . $ext;
                $path = $file->storeAs('dokumen_persyaratan', $randomFilename, 'local');

                Dokumen::updateOrCreate(
                    [
                        'id_calon_siswa' => $casis->id_calon_siswa,
                        'jenis_dokumen'  => $docType,
                    ],
                    [
                        'file_path'         => $path,
                        'tgl_upload'        => now(),
                        'status_verifikasi' => 'Menunggu',
                        'catatan'           => null,
                    ]
                );
            }
        }

        app(VerifikasiDokumenService::class)->sinkronisasiStatusPendaftaran($casis->id_calon_siswa);

        return redirect()->route('indexcasis')->with('success', 'Data calon siswa berhasil diperbarui.');
    }

    public function form()
    {
        $user = Auth::user();
        $user->load(['calonSiswa.dokumen', 'calonSiswa.tahunAjaran']);
        $archivedCasis = $user->calonSiswa ? null : $user->calonSiswa()->onlyTrashed()->with(['dokumen', 'tahunAjaran'])->first();
        $isArchived = !empty($archivedCasis);

        $tahunAjarBerlangsung = TahunAjaran::where('status', 'Berlangsung')->first();
        $kuotaTersedia = false;
        $pesanKuota = '';

        if ($tahunAjarBerlangsung) {
            $jumlahPendaftar = CalonSiswa::where('id_tahun_ajaran', $tahunAjarBerlangsung->id_tahun_ajaran)->count();

            if ($jumlahPendaftar < $tahunAjarBerlangsung->kuota) {
                $kuotaTersedia = true;
                $sisaKuota = $tahunAjarBerlangsung->kuota - $jumlahPendaftar;
                $pesanKuota = "Kuota tersisa: {$sisaKuota} dari {$tahunAjarBerlangsung->kuota}";
            } else {
                $pesanKuota = "Mohon maaf, kuota pendaftaran untuk tahun ajaran {$tahunAjarBerlangsung->tahun_ajaran} sudah penuh.";
            }
        } else {
            $pesanKuota = "Tidak ada tahun ajaran yang sedang berlangsung saat ini.";
        }

        return view('casis.data', compact('user', 'kuotaTersedia', 'pesanKuota', 'archivedCasis', 'isArchived'));
    }

    public function daftar()
    {
        $user = Auth::user();
        if ($user->calonSiswa()->onlyTrashed()->exists()) {
            return redirect()->route('formcasis')->with('error', 'Data pendaftaran Anda sedang diarsipkan. Silakan hubungi panitia.');
        }

        $user->load(['calonSiswa.dokumen', 'calonSiswa.tahunAjaran']);

        return view('casis.daftar', compact('user'));
    }

    public function proses(Request $request)
    {
        // PENEGAKAN KEAMANAN SERVER: Tolak jika data pendaftaran calon siswa sedang diarsipkan
        if (CalonSiswa::onlyTrashed()->where('id_user', Auth::user()->id_user)->exists()) {
            return redirect()->route('formcasis')->with('error', 'Data pendaftaran Anda sedang diarsipkan. Anda tidak dapat membuat pendaftaran baru. Silakan hubungi panitia.');
        }

        try {
            DB::beginTransaction();

            // Mendapatkan tahun ajar yang berlangsung
            $tahunAjarBerlangsung = TahunAjaran::where('status', 'Berlangsung')->firstOrFail();

            // Cek apakah calon siswa sudah pernah mendaftar sebelumnya
            $existingCasis = CalonSiswa::with('pembayaran')->where('id_user', Auth::user()->id_user)->first();

            // PENEGAKAN KEAMANAN SERVER: Tolak perubahan data jika status sudah Diterima atau pembayaran sudah Lunas
            if ($existingCasis) {
                $isLunas = ($existingCasis->pembayaran && $existingCasis->pembayaran->status_pembayaran === 'Lunas');
                $isDiterima = ($existingCasis->status_pendaftaran === 'Diterima');

                if ($isDiterima || $isLunas) {
                    throw ValidationException::withMessages([
                        'pendaftaran' => 'Data pendaftaran dan berkas persyaratan telah terverifikasi/lunas dan terkunci. Perubahan tidak dapat dilakukan melalui formulir ini. Silakan hubungi panitia PPDB.',
                    ]);
                }
            }

            // Memeriksa kuota jika pendaftaran baru
            if (!$existingCasis) {
                $jumlahPendaftar = CalonSiswa::where('id_tahun_ajaran', $tahunAjarBerlangsung->id_tahun_ajaran)->count();
                if ($jumlahPendaftar >= $tahunAjarBerlangsung->kuota) {
                    throw new \Exception('Mohon maaf, kuota pendaftaran untuk tahun ajaran ini sudah penuh.');
                }
            }

            // Validasi input
            $validatedData = $request->validate([
                'nik' => 'required|string|max:20',
                'nama' => 'required|string|max:225',
                'tempat_lahir' => 'required|string|max:225',
                'tanggal_lahir' => 'required|date',
                'agama' => 'nullable|string|max:50',
                'jenis_kelamin' => 'required|in:Laki-Laki,Perempuan',
                'status_anak' => 'nullable|in:Anak Kandung,Anak Tiri,Anak Angkat',
                'jml_saudara' => 'required|integer',
                'alamat' => 'required|string|max:225',
                'no_hp_calon_siswa' => 'nullable|string|max:20',
                
                // Data Ayah
                'nama_ayah' => 'nullable|string|max:225',
                'pendidikan_ayah' => 'nullable|in:SD,SMP,SMA/SMK,D3,S1,S2,S3',
                'pekerjaan_ayah' => 'nullable|string|max:225',
                'penghasilan_ayah' => 'nullable|numeric',

                // Data Ibu
                'nama_ibu' => 'nullable|string|max:225',
                'pendidikan_ibu' => 'nullable|in:SD,SMP,SMA/SMK,D3,S1,S2,S3',
                'pekerjaan_ibu' => 'nullable|string|max:225',
                'penghasilan_ibu' => 'nullable|numeric',

                // Kontak Ortu
                'no_hp' => 'required|string|max:20',

                // Dokumen
                'akte' => 'nullable|file',
                'kk'   => 'nullable|file',
                'foto' => 'nullable|file',
                'kps'  => 'nullable|file',
            ], [
                'nik.required' => 'NIK wajib diisi.',
                'nama.required' => 'Nama lengkap wajib diisi.',
                'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
                'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
                'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
                'jml_saudara.required' => 'Jumlah saudara wajib diisi.',
                'alamat.required' => 'Alamat lengkap wajib diisi.',
                'no_hp.required' => 'Nomor HP / WhatsApp wajib diisi.',
            ]);

            // Format data calon siswa untuk tb_calon_siswa
            $casisPayload = [
                'id_user'             => Auth::user()->id_user,
                'id_tahun_ajaran'     => $tahunAjarBerlangsung->id_tahun_ajaran,
                'nik_calon_siswa'     => $request->nik,
                'nama_calon_siswa'    => $request->nama,
                'tmpt_lahir'          => $request->tempat_lahir,
                'tgl_lahir'           => $request->tanggal_lahir,
                'agama'               => $request->agama ?? 'Kristen Protestan',
                'jmlh_saudara'        => $request->jml_saudara,
                'jenis_kelamin'       => $request->jenis_kelamin,
                'status_anak'         => $request->status_anak ?? 'Anak Kandung',
                'alamat_calon_siswa'  => $request->alamat,
                'no_hp_calon_siswa'   => $request->no_hp_calon_siswa,
                'nama_ayah'           => $request->nama_ayah ?: $request->nama_ortu,
                'nama_ibu'            => $request->nama_ibu,
                'pendidikan_ayah'     => $request->pendidikan_ayah ?: $request->pendidikan_ortu,
                'pendidikan_ibu'      => $request->pendidikan_ibu,
                'pekerjaan_ayah'      => $request->pekerjaan_ayah ?: $request->pekerjaan_ortu,
                'pekerjaan_ibu'       => $request->pekerjaan_ibu,
                'penghasilan_ayah'    => $request->penghasilan_ayah ?: $request->gaji_ortu,
                'penghasilan_ibu'     => $request->penghasilan_ibu,
                'no_hp_orang_tua'     => $request->no_hp,
                'tgl_pendaftaran'     => $existingCasis?->tgl_pendaftaran ?? now(),
                'status_pendaftaran'  => $existingCasis?->status_pendaftaran ?? 'Menunggu',
            ];

            // Menyimpan atau memperbarui data calon siswa
            $casis = CalonSiswa::updateOrCreate(
                ['id_user' => Auth::user()->id_user],
                $casisPayload
            );

            // Proses upload dokumen berkas ke tb_dokumen
            $documentMap = [
                'akte' => 'Akta Kelahiran',
                'kk'   => 'Kartu Keluarga',
                'foto' => 'Pas Foto',
                'kps'  => 'KPS',
            ];

            foreach ($documentMap as $inputKey => $docType) {
                if ($request->hasFile($inputKey)) {
                    $existingDoc = Dokumen::where('id_calon_siswa', $casis->id_calon_siswa)
                        ->where('jenis_dokumen', $docType)
                        ->first();

                    // Kunci dokumen yang sudah Valid: orang tua tidak diizinkan menimpa dokumen yang sudah disetujui
                    if ($existingDoc && $existingDoc->status_verifikasi === 'Valid') {
                        continue;
                    }

                    $statusLama = $existingDoc?->status_verifikasi ?? 'Menunggu';

                    $file = $request->file($inputKey);

                    // Validasi ketat berdasarkan isi berkas (finfo & magic byte)
                    DokumenSecurityValidator::validate($file, $inputKey);

                    // Simpan di disk lokal privat dengan UUID acak tanpa ID/timestamp
                    $ext = strtolower($file->getClientOriginalExtension());
                    $randomFilename = Str::uuid()->toString() . '.' . $ext;
                    $path = $file->storeAs('dokumen_persyaratan', $randomFilename, 'local');

                    $doc = Dokumen::updateOrCreate(
                        [
                            'id_calon_siswa' => $casis->id_calon_siswa,
                            'jenis_dokumen'  => $docType,
                        ],
                        [
                            'file_path'         => $path,
                            'tgl_upload'        => now(),
                            'status_verifikasi' => 'Menunggu',
                            'catatan'           => null,
                        ]
                    );

                    // Catat ke log audit bila sebelumnya berstatus Tidak Valid
                    if ($statusLama === 'Tidak Valid') {
                        LogVerifikasiDokumen::create([
                            'id_dokumen'     => $doc->id_dokumen,
                            'id_calon_siswa' => $casis->id_calon_siswa,
                            'id_user_admin'  => Auth::user()->id_user,
                            'status_lama'    => 'Tidak Valid',
                            'status_baru'    => 'Menunggu',
                            'catatan'        => 'Unggah ulang dokumen perbaikan oleh orang tua.',
                            'created_at'     => now(),
                        ]);
                    }
                }
            }

            // Hitung ulang status pendaftaran secara otomatis setelah berkas diunggah
            app(VerifikasiDokumenService::class)->sinkronisasiStatusPendaftaran($casis->id_calon_siswa);

            DB::commit();

            return redirect()->route('formcasis')->with('success', 'Data pendaftaran dan berkas persyaratan berhasil disimpan.');
        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Terjadi kesalahan pada input data. Silakan periksa kembali.');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Tahun ajaran tidak ditemukan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Tidak ada tahun ajaran yang sedang berlangsung.')
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saat menyimpan pendaftaran: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    public function deletecasis(Request $request, $id)
    {
        // 1. Otorisasi ketat hanya untuk Admin
        if (!Auth::check() || Auth::user()->role !== 'Admin') {
            abort(403, 'Hanya Administrator yang memiliki wewenang untuk menghapus atau mengarsipkan data calon siswa.');
        }

        $casis = CalonSiswa::withTrashed()->findOrFail($id);
        $action = $request->input('action', 'archive'); // 'archive' atau 'force_delete'

        // 2. Periksa apakah siswa memiliki riwayat pembayaran, seleksi, atau daftar ulang
        $hasPembayaran = $casis->pembayaran()->exists();
        $hasSeleksi = $casis->pembayaran && $casis->pembayaran->seleksi()->exists();
        $hasDaftarUlang = $hasSeleksi && $casis->pembayaran->seleksi->daftarUlang()->exists();
        $hasRelatedData = $hasPembayaran || $hasSeleksi || $hasDaftarUlang;

        // 3. Jalur Hapus Permanen
        if ($action === 'force_delete') {
            if ($hasRelatedData) {
                return redirect()->back()->with('error', 'Data calon siswa tidak dapat dihapus permanen karena sudah memiliki riwayat pembayaran, seleksi, atau daftar ulang. Silakan gunakan opsi Arsipkan.');
            }

            $studentName = $casis->nama_calon_siswa ?: $casis->nama;
            $filePaths = [];

            DB::beginTransaction();
            try {
                // Kumpulkan berkas fisik dokumen sebelum record dokumen dihapus
                $filePaths = $casis->dokumen()->pluck('file_path')->filter()->all();

                // Hapus dokumen di database
                $casis->dokumen()->delete();

                // Hapus data calon siswa secara permanen
                $casis->forceDelete();

                // Akun tb_users TIDAK dihapus otomatis demi menjaga integritas akun

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Gagal menghapus permanen calon siswa ID {$id}: " . $e->getMessage());
                return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menghapus data calon siswa.');
            }

            // Hapus file fisik dari disk privat (local) dan fallback public SETELAH commit berhasil
            foreach ($filePaths as $path) {
                try {
                    if (Storage::disk('local')->exists($path)) {
                        Storage::disk('local')->delete($path);
                    } elseif (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                } catch (\Throwable $fileErr) {
                    Log::warning("Gagal menghapus file fisik dokumen {$path} pada disk: " . $fileErr->getMessage());
                }
            }

            // Audit log (tanpa NIK utuh, no HP, atau isi dokumen)
            Log::info('Audit Admin: Data calon siswa dihapus permanen', [
                'admin_id' => Auth::id(),
                'id_calon_siswa' => $id,
                'action' => 'hapus_permanen',
                'time' => now()->toIso8601String(),
            ]);

            return redirect()->route('indexcasis')->with('success', "Data calon siswa {$studentName} berhasil dihapus permanen.");
        }

        // 4. Jalur Arsip (Soft Delete)
        $studentName = $casis->nama_calon_siswa ?: $casis->nama;
        $casis->delete();

        // Audit log
        Log::info('Audit Admin: Data calon siswa diarsipkan', [
            'admin_id' => Auth::id(),
            'id_calon_siswa' => $id,
            'action' => 'arsip',
            'time' => now()->toIso8601String(),
        ]);

        return redirect()->route('indexcasis')->with('success', "Data calon siswa {$studentName} berhasil diarsipkan.");
    }

    /**
     * Pulihkan data calon siswa yang terarsip (soft-deleted)
     */
    public function restorecasis(Request $request, $id)
    {
        // 1. Otorisasi ketat hanya untuk Admin
        if (!Auth::check() || Auth::user()->role !== 'Admin') {
            abort(403, 'Hanya Administrator yang memiliki wewenang untuk memulihkan data calon siswa.');
        }

        $casis = CalonSiswa::onlyTrashed()->findOrFail($id);
        $studentName = $casis->nama_calon_siswa ?: $casis->nama;

        $casis->restore();

        // Audit log
        Log::info('Audit Admin: Data calon siswa dipulihkan', [
            'admin_id' => Auth::id(),
            'id_calon_siswa' => $id,
            'action' => 'pulihkan',
            'time' => now()->toIso8601String(),
        ]);

        return redirect()->route('indexcasis', ['status_arsip' => 'aktif'])
            ->with('success', "Data calon siswa {$studentName} berhasil dipulihkan dari arsip.");
    }
}
