<?php

namespace App\Http\Controllers;

use App\Models\CalonSiswa;
use App\Models\Pembayaran;
use App\Models\Seleksi;
use App\Models\TahunAjaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LaporanController extends Controller
{
    /**
     * Helper data tanda tangan laporan dua kolom (Kepala Sekolah & Petugas)
     */
    protected function resolveSignatureData(string $defaultRole = 'Administrator'): array
    {
        $namaKepalaSekolah = TahunAjaran::where('status', 'Berlangsung')->value('nama_kepala_sekolah')
            ?? config('sekolah.kepala_sekolah', 'Serni, S.Pd.K');

        $user = auth()->user();
        $authRole = $user?->role ?? null;
        $jabatanPetugas = ($authRole === 'Panitia') ? 'Panitia PPDB' : ($authRole === 'Admin' ? 'Administrator' : $defaultRole);
        $namaPetugas = $user?->nama ?? ($user?->name ?? $jabatanPetugas);

        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $now = \Carbon\Carbon::now();
        $tanggalCetak = $now->day . ' ' . $bulanIndo[$now->month] . ' ' . $now->year;
        $kotaSekolah = config('sekolah.kota', 'Rantai Damai');

        return compact('namaKepalaSekolah', 'jabatanPetugas', 'namaPetugas', 'tanggalCetak', 'kotaSekolah');
    }

    /**
     * Membangun query data pendaftaran calon siswa dengan filter dinamis
     */
    protected function buildPendaftaranQuery(Request $request)
    {
        $query = CalonSiswa::with(['tahunAjaran', 'pembayaran', 'user']);

        // Filter Rentang Tanggal Pendaftaran
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_pendaftaran', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->where('tgl_pendaftaran', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('tgl_pendaftaran', '<=', $request->end_date);
        }

        // Filter Status Pendaftaran
        if ($request->filled('filter_program')) {
            $status = $request->filter_program;
            $mappedStatus = match (strtoupper($status)) {
                'BERHASIL', 'DITERIMA' => 'Diterima',
                'GAGAL', 'DITOLAK' => 'Ditolak',
                'PENDING', 'MENUNGGU' => 'Menunggu',
                default => $status,
            };
            $query->where('status_pendaftaran', $mappedStatus);
        }

        // Filter Tahun Ajaran
        if ($request->filled('id_tahun_ajaran')) {
            $query->where('id_tahun_ajaran', $request->id_tahun_ajaran);
        }

        // Filter Pencarian (Nama, No. Registrasi, NIK)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_calon_siswa', 'like', "%{$search}%")
                  ->orWhere('no_registrasi', 'like', "%{$search}%")
                  ->orWhere('nik_calon_siswa', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('id_calon_siswa', 'desc');
    }

    /**
     * Laporan Pendaftaran Calon Siswa
     */
    public function pendaftaran(Request $request)
    {
        $data = $this->buildPendaftaranQuery($request)->get();

        // Simpan data & filter di session untuk unduh PDF
        Session::put('pendaftaran_data', $data);
        Session::put('pendaftaran_filter', $request->only(['start_date', 'end_date', 'filter_program', 'id_tahun_ajaran', 'search']));

        return view('laporan.pendaftaran', compact('data'));
    }

    /**
     * Unduh PDF Laporan Pendaftaran Calon Siswa
     */
    public function unduhpendaftaran(Request $request)
    {
        $hasUrlFilters = $request->filled('start_date') || 
                         $request->filled('end_date') || 
                         $request->filled('filter_program') || 
                         $request->filled('id_tahun_ajaran') || 
                         $request->filled('search');

        if ($hasUrlFilters) {
            $data = $this->buildPendaftaranQuery($request)->get();
            $activeFilter = $request->only(['start_date', 'end_date', 'filter_program', 'id_tahun_ajaran', 'search']);
        } else {
            $savedFilter = Session::get('pendaftaran_filter', []);
            if (!empty($savedFilter) && collect($savedFilter)->filter()->isNotEmpty()) {
                $fakeRequest = new Request($savedFilter);
                $data = $this->buildPendaftaranQuery($fakeRequest)->get();
                $activeFilter = $savedFilter;
            } elseif (Session::has('pendaftaran_data')) {
                $data = Session::get('pendaftaran_data');
                $activeFilter = Session::get('pendaftaran_filter', []);
            } else {
                $data = $this->buildPendaftaranQuery($request)->get();
                $activeFilter = [];
            }
        }

        $filterTahunAjaran = null;
        if (!empty($activeFilter['id_tahun_ajaran'])) {
            $filterTahunAjaran = TahunAjaran::find($activeFilter['id_tahun_ajaran']);
        }

        $sig = $this->resolveSignatureData('Administrator');
        $pdf = Pdf::loadView('laporan.unduhpendaftaran', array_merge(compact('data', 'activeFilter', 'filterTahunAjaran'), $sig))
                  ->setPaper('a4', 'landscape');

        $filename = 'laporan_pendaftaran';
        if (!empty($activeFilter['filter_program'])) {
            $filename .= '_' . strtolower(str_replace(' ', '_', $activeFilter['filter_program']));
        }
        if ($filterTahunAjaran) {
            $filename .= '_ta_' . str_replace('/', '-', $filterTahunAjaran->tahun_ajaran);
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Membangun query data pembayaran dengan filter dinamis
     */
    protected function buildPembayaranQuery(Request $request)
    {
        $query = Pembayaran::with(['calonSiswa.tahunAjaran']);

        // Filter Rentang Tanggal Pembayaran
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_pembayaran', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        } elseif ($request->filled('start_date')) {
            $query->where('tgl_pembayaran', '>=', $request->start_date . ' 00:00:00');
        } elseif ($request->filled('end_date')) {
            $query->where('tgl_pembayaran', '<=', $request->end_date . ' 23:59:59');
        }

        // Filter Status Pembayaran
        if ($request->filled('filter_program')) {
            $status = $request->filter_program;
            if (in_array(strtoupper($status), ['LUNAS', 'BERHASIL', 'SETTLEMENT', 'SUCCESS'])) {
                $query->whereIn('status_pembayaran', ['Lunas', 'Diterima', 'Berhasil', 'Settlement', 'success']);
            } elseif (in_array(strtoupper($status), ['PENDING', 'MENUNGGU', 'BELUM BAYAR'])) {
                $query->whereIn('status_pembayaran', ['Menunggu', 'Pending', 'Belum Bayar', 'pending']);
            } elseif (in_array(strtoupper($status), ['GAGAL', 'DITOLAK', 'EXPIRE', 'CANCEL'])) {
                $query->whereIn('status_pembayaran', ['Ditolak', 'Gagal', 'expire', 'cancel']);
            } else {
                $query->where('status_pembayaran', $status);
            }
        }

        // Filter Tahun Ajaran
        if ($request->filled('id_tahun_ajaran')) {
            $query->whereHas('calonSiswa', function ($q) use ($request) {
                $q->where('id_tahun_ajaran', $request->id_tahun_ajaran);
            });
        }

        // Filter Pencarian (Order ID, Nama Calon Siswa, No. Registrasi)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                  ->orWhereHas('calonSiswa', function ($casisQuery) use ($search) {
                      $casisQuery->where('nama_calon_siswa', 'like', "%{$search}%")
                                 ->orWhere('no_registrasi', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('id_pembayaran', 'desc');
    }

    /**
     * Laporan Pembayaran Pendaftaran
     */
    public function pembayaran(Request $request)
    {
        $data = $this->buildPembayaranQuery($request)->get();

        // Simpan data & filter di session
        Session::put('pembayaran_data', $data);
        Session::put('pembayaran_filter', $request->only(['start_date', 'end_date', 'filter_program', 'id_tahun_ajaran', 'search']));

        return view('laporan.pembayaran', compact('data'));
    }

    /**
     * Unduh PDF Laporan Pembayaran
     */
    public function unduhpembayaran(Request $request)
    {
        $hasUrlFilters = $request->filled('start_date') || 
                         $request->filled('end_date') || 
                         $request->filled('filter_program') || 
                         $request->filled('id_tahun_ajaran') || 
                         $request->filled('search');

        if ($hasUrlFilters) {
            $data = $this->buildPembayaranQuery($request)->get();
            $activeFilter = $request->only(['start_date', 'end_date', 'filter_program', 'id_tahun_ajaran', 'search']);
        } else {
            $savedFilter = Session::get('pembayaran_filter', []);
            if (!empty($savedFilter) && collect($savedFilter)->filter()->isNotEmpty()) {
                $fakeRequest = new Request($savedFilter);
                $data = $this->buildPembayaranQuery($fakeRequest)->get();
                $activeFilter = $savedFilter;
            } elseif (Session::has('pembayaran_data')) {
                $data = Session::get('pembayaran_data');
                $activeFilter = Session::get('pembayaran_filter', []);
            } else {
                $data = $this->buildPembayaranQuery($request)->get();
                $activeFilter = [];
            }
        }

        $filterTahunAjaran = null;
        if (!empty($activeFilter['id_tahun_ajaran'])) {
            $filterTahunAjaran = TahunAjaran::find($activeFilter['id_tahun_ajaran']);
        }

        $sig = $this->resolveSignatureData('Administrator');
        $pdf = Pdf::loadView('laporan.unduhpembayaran', array_merge(compact('data', 'activeFilter', 'filterTahunAjaran'), $sig))
                  ->setPaper('a4', 'landscape');

        $filename = 'laporan_pembayaran';
        if (!empty($activeFilter['filter_program'])) {
            $filename .= '_' . strtolower(str_replace(' ', '_', $activeFilter['filter_program']));
        }
        if ($filterTahunAjaran) {
            $filename .= '_ta_' . str_replace('/', '-', $filterTahunAjaran->tahun_ajaran);
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Data Cetak Detail Calon Siswa
     */
    public function datacasis(Request $request)
    {
        $id = $request->id;
        $casis = null;

        if ($id) {
            $casis = CalonSiswa::with(['tahunAjaran', 'pembayaran', 'dokumen'])->find($id);
        }

        if (!$casis) {
            $casis = CalonSiswa::with(['tahunAjaran', 'pembayaran', 'dokumen'])->latest('id_calon_siswa')->first();
        }

        return view('laporan.datacasis', compact('casis'));
    }

    /**
     * Membangun query data hasil seleksi dengan filter dinamis
     */
    protected function buildHasilCasisQuery(Request $request)
    {
        $query = Seleksi::with(['pembayaran.calonSiswa.tahunAjaran']);

        // Filter Rentang Tanggal Penilaian/Seleksi
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_seleksi', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->where('tgl_seleksi', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('tgl_seleksi', '<=', $request->end_date);
        }

        // Filter Status Kelulusan
        if ($request->filled('status')) {
            if ($request->status === 'Lulus') {
                $query->whereIn('hasil_seleksi', ['Lulus', 'Lolos']);
            } elseif ($request->status === 'Tidak Lulus') {
                $query->whereIn('hasil_seleksi', ['Tidak Lulus', 'Gagal', 'Tidak Lolos']);
            }
        }

        // Filter Tahun Ajaran
        if ($request->filled('id_tahun_ajaran')) {
            $query->whereHas('pembayaran.calonSiswa', function ($q) use ($request) {
                $q->where('id_tahun_ajaran', $request->id_tahun_ajaran);
            });
        }

        // Filter Pencarian (Nama, No. Registrasi, NIK)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('pembayaran.calonSiswa', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_calon_siswa', 'like', "%{$search}%")
                        ->orWhere('no_registrasi', 'like', "%{$search}%")
                        ->orWhere('nik_calon_siswa', 'like', "%{$search}%");
                });
            });
        }

        return $query->orderBy('id_seleksi', 'desc');
    }

    /**
     * Laporan Hasil Seleksi Calon Siswa
     */
    public function hasilcasis(Request $request)
    {
        $data = $this->buildHasilCasisQuery($request)->get();

        // Hitung statistik rekapitulasi data yang terfilter
        $totalPeserta = $data->count();
        $totalLulus = $data->filter(fn($item) => in_array($item->hasil_seleksi, ['Lulus', 'Lolos']))->count();
        $totalTidakLulus = $data->filter(fn($item) => in_array($item->hasil_seleksi, ['Tidak Lulus', 'Gagal', 'Tidak Lolos']))->count();
        $rataRataNilai = $totalPeserta > 0 ? round($data->avg('total_nilai'), 1) : 0;

        $tahunAjaranList = TahunAjaran::orderBy('id_tahun_ajaran', 'desc')->get();

        // Simpan data & filter ke Session agar unduhan PDF sesuai dengan kriteria yang dipilih panitia
        Session::put('seleksi_data', $data);
        Session::put('seleksi_filter', $request->only(['start_date', 'end_date', 'status', 'id_tahun_ajaran', 'search']));

        return view('laporan.hasilcasis', compact(
            'data',
            'totalPeserta',
            'totalLulus',
            'totalTidakLulus',
            'rataRataNilai',
            'tahunAjaranList'
        ));
    }

    /**
     * Unduh PDF Hasil Seleksi Sesuai Filter
     */
    public function unduhhasil(Request $request)
    {
        // 1. Cek apakah ada parameter filter langsung di URL
        $hasUrlFilters = $request->filled('start_date') || 
                         $request->filled('end_date') || 
                         $request->filled('status') || 
                         $request->filled('id_tahun_ajaran') || 
                         $request->filled('search');

        if ($hasUrlFilters) {
            $data = $this->buildHasilCasisQuery($request)->get();
            $activeFilter = $request->only(['start_date', 'end_date', 'status', 'id_tahun_ajaran', 'search']);
        } else {
            // 2. Jika tidak ada di URL, gunakan filter dari Session hasilcasis sebelumnya
            $savedFilter = Session::get('seleksi_filter', []);
            if (!empty($savedFilter) && collect($savedFilter)->filter()->isNotEmpty()) {
                $fakeRequest = new Request($savedFilter);
                $data = $this->buildHasilCasisQuery($fakeRequest)->get();
                $activeFilter = $savedFilter;
            } elseif (Session::has('seleksi_data')) {
                $data = Session::get('seleksi_data');
                $activeFilter = Session::get('seleksi_filter', []);
            } else {
                $data = $this->buildHasilCasisQuery($request)->get();
                $activeFilter = [];
            }
        }

        if ($data->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data hasil seleksi untuk kriteria filter yang dipilih.');
        }

        // Ambil data Tahun Ajaran untuk judul dokumen PDF bila difilter
        $filterTahunAjaran = null;
        if (!empty($activeFilter['id_tahun_ajaran'])) {
            $filterTahunAjaran = TahunAjaran::find($activeFilter['id_tahun_ajaran']);
        }

        $sig = $this->resolveSignatureData('Panitia PPDB');
        $pdf = Pdf::loadView('laporan.unduhseleksi', array_merge(compact('data', 'activeFilter', 'filterTahunAjaran'), $sig))
                  ->setPaper('a4', 'landscape');

        // Nama file dinamis mencerminkan filter yang dipilih
        $filename = 'laporan_hasil_seleksi';
        if (!empty($activeFilter['status'])) {
            $filename .= '_' . strtolower(str_replace(' ', '_', $activeFilter['status']));
        }
        if ($filterTahunAjaran) {
            $filename .= '_ta_' . str_replace('/', '-', $filterTahunAjaran->tahun_ajaran);
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Laporan Calon Siswa Lulus / Diterima
     */
    public function diterimacasis(Request $request)
    {
        $query = Seleksi::with(['pembayaran.calonSiswa.tahunAjaran'])
            ->whereNotNull('diumumkan_pada')
            ->whereIn('hasil_seleksi', ['Lulus', 'Lolos']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_seleksi', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->where('tgl_seleksi', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('tgl_seleksi', '<=', $request->end_date);
        }

        $data = $query->orderBy('id_seleksi', 'desc')->get();

        Session::put('seleksi_diterima', $data);
        Session::put('seleksi_diterima_filter', $request->only(['start_date', 'end_date']));

        return view('laporan.diterima', compact('data'));
    }

    /**
     * Unduh PDF Calon Siswa Diterima
     */
    public function unduhditerima(Request $request)
    {
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $query = Seleksi::with(['pembayaran.calonSiswa.tahunAjaran'])
                ->whereNotNull('diumumkan_pada')
                ->whereIn('hasil_seleksi', ['Lulus', 'Lolos']);

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('tgl_seleksi', [$request->start_date, $request->end_date]);
            } elseif ($request->filled('start_date')) {
                $query->where('tgl_seleksi', '>=', $request->start_date);
            } elseif ($request->filled('end_date')) {
                $query->where('tgl_seleksi', '<=', $request->end_date);
            }
            $data = $query->orderBy('id_seleksi', 'desc')->get();
        } else {
            $data = Session::get('seleksi_diterima');
            if (!$data || $data->isEmpty()) {
                $data = Seleksi::with(['pembayaran.calonSiswa'])
                    ->whereNotNull('diumumkan_pada')
                    ->whereIn('hasil_seleksi', ['Lulus', 'Lolos'])
                    ->orderBy('id_seleksi', 'desc')
                    ->get();
            }
        }

        if ($data->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data siswa diterima untuk diunduh.');
        }

        $sig = $this->resolveSignatureData('Panitia PPDB');
        $pdf = Pdf::loadView('laporan.unduhditerima', array_merge(compact('data'), $sig))->setPaper('a4', 'landscape');

        return $pdf->download('laporan_siswa_diterima.pdf');
    }

    /**
     * Laporan Calon Siswa Tidak Lulus / Ditolak
     */
    public function gagalcasis(Request $request)
    {
        $query = Seleksi::with(['pembayaran.calonSiswa.tahunAjaran'])
            ->whereNotNull('diumumkan_pada')
            ->whereIn('hasil_seleksi', ['Tidak Lulus', 'Tidak Lolos', 'Ditolak']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_seleksi', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->where('tgl_seleksi', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('tgl_seleksi', '<=', $request->end_date);
        }

        $data = $query->orderBy('id_seleksi', 'desc')->get();

        Session::put('seleksi_gagal', $data);
        Session::put('seleksi_gagal_filter', $request->only(['start_date', 'end_date']));

        return view('laporan.gagal', compact('data'));
    }

    /**
     * Unduh PDF Calon Siswa Tidak Lulus
     */
    public function unduhgagal(Request $request)
    {
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $query = Seleksi::with(['pembayaran.calonSiswa.tahunAjaran'])
                ->whereNotNull('diumumkan_pada')
                ->whereIn('hasil_seleksi', ['Tidak Lulus', 'Tidak Lolos', 'Ditolak']);

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('tgl_seleksi', [$request->start_date, $request->end_date]);
            } elseif ($request->filled('start_date')) {
                $query->where('tgl_seleksi', '>=', $request->start_date);
            } elseif ($request->filled('end_date')) {
                $query->where('tgl_seleksi', '<=', $request->end_date);
            }
            $data = $query->orderBy('id_seleksi', 'desc')->get();
        } else {
            $data = Session::get('seleksi_gagal');
            if (!$data || $data->isEmpty()) {
                $data = Seleksi::with(['pembayaran.calonSiswa'])
                    ->whereNotNull('diumumkan_pada')
                    ->whereIn('hasil_seleksi', ['Tidak Lulus', 'Tidak Lolos', 'Ditolak'])
                    ->orderBy('id_seleksi', 'desc')
                    ->get();
            }
        }

        if ($data->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data siswa tidak lulus untuk diunduh.');
        }

        $sig = $this->resolveSignatureData('Panitia PPDB');
        $pdf = Pdf::loadView('laporan.unduhgagal', array_merge(compact('data'), $sig))->setPaper('a4', 'landscape');

        return $pdf->download('laporan_siswa_tidak_lulus.pdf');
    }
}
