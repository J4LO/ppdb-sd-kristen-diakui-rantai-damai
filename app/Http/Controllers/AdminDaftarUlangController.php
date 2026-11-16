<?php

namespace App\Http\Controllers;

use App\Models\CalonSiswa;
use App\Models\DaftarUlang;
use App\Models\PembayaranDaftarUlang;
use App\Models\TahunAjaran;
use App\Services\MidtransService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminDaftarUlangController extends Controller
{
    protected MidtransService $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Menampilkan daftar calon siswa yang Lulus Seleksi dan Resmi Diumumkan untuk Daftar Ulang & Uang Pangkal
     */
    public function index(Request $request)
    {
        if (!Auth::check() || Auth::user()?->role !== 'Admin') {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // 1. Auto-sync untuk transaksi online yang masih berstatus Pending/Menunggu
        $pendingPayments = PembayaranDaftarUlang::whereIn('status_du', ['Menunggu', 'Pending'])
            ->whereNotNull('order_id_du')
            ->take(10)
            ->get();

        foreach ($pendingPayments as $p) {
            $this->syncPaymentStatus($p, $p->daftarUlang);
        }

        // 2. Query SEMUA siswa yang Lulus dan sudah diumumkan (tidak terarsip)
        $query = CalonSiswa::query()
            ->whereNull('tb_calon_siswa.deleted_at')
            ->whereHas('seleksi', function ($q) {
                $q->whereIn('hasil_seleksi', ['Lulus', 'Lolos'])
                  ->whereNotNull('diumumkan_pada');
            })
            ->with([
                'tahunAjaran',
                'seleksi.daftarUlang.pembayaranDaftarUlang',
            ]);

        // Filter Tahun Ajaran
        if ($request->filled('tahun_ajaran')) {
            $query->where('id_tahun_ajaran', $request->tahun_ajaran);
        }

        // Pencarian mencakup: Nama, No. Registrasi, NIK
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_calon_siswa', 'like', "%{$search}%")
                  ->orWhere('no_registrasi', 'like', "%{$search}%")
                  ->orWhere('nik_calon_siswa', 'like', "%{$search}%");
            });
        }

        // Ambil semua calon siswa yang sesuai kriteria seleksi & pencarian
        $allCandidates = $query->orderBy('id_calon_siswa', 'desc')->get();

        // 3. Dekorasi setiap calon siswa dengan aturan bisnis PPDB
        $decoratedAll = $allCandidates->map(function ($casis) {
            return $this->decorateCasisDaftarUlang($casis);
        });

        // 4. Hitung Metrik Ringkasan (Summary) & Tab Counts
        $totalLulus = $decoratedAll->count();
        $totalBelumBayar = $decoratedAll->where('statusKeuangan', 'Belum Bayar')->count();
        $totalMenungguBerkas = $decoratedAll->where('statusDaftarUlang', 'Menunggu Berkas')->count();
        $totalTerlambat = $decoratedAll->where('statusTahap2', 'Terlambat')->count();
        $totalSelesai = $decoratedAll->where('statusDaftarUlang', 'Selesai')->count();

        $totalNominalTagihan = $decoratedAll->sum('totalBiaya');
        $totalNominalTerbayar = $decoratedAll->sum('totalTerbayar');
        $totalNominalSisa = $decoratedAll->sum('sisaBiaya');

        $tabCounts = [
            'semua' => $totalLulus,
            'belum_bayar' => $totalBelumBayar,
            'menunggu_berkas' => $totalMenungguBerkas,
            'terlambat' => $totalTerlambat,
            'selesai' => $totalSelesai,
        ];

        $summary = [
            'totalLulus' => $totalLulus,
            'totalBelumBayar' => $totalBelumBayar,
            'totalMenungguBerkas' => $totalMenungguBerkas,
            'totalTerlambat' => $totalTerlambat,
            'totalSelesai' => $totalSelesai,
            'totalTagihan' => $totalNominalTagihan,
            'totalTerbayar' => $totalNominalTerbayar,
            'totalSisa' => $totalNominalSisa,
        ];

        // 5. Filter Koleksi berdasarkan Tab Aksi
        $activeTab = $request->get('tab', 'semua');
        $filtered = $decoratedAll;

        if ($activeTab === 'belum_bayar') {
            $filtered = $filtered->where('statusKeuangan', 'Belum Bayar');
        } elseif ($activeTab === 'menunggu_berkas') {
            $filtered = $filtered->where('statusDaftarUlang', 'Menunggu Berkas');
        } elseif ($activeTab === 'terlambat') {
            $filtered = $filtered->where('statusTahap2', 'Terlambat');
        } elseif ($activeTab === 'selesai') {
            $filtered = $filtered->where('statusDaftarUlang', 'Selesai');
        }

        // 6. Filter Koleksi berdasarkan Dropdown Tambahan
        if ($request->filled('status_keuangan')) {
            $filtered = $filtered->where('statusKeuangan', $request->status_keuangan);
        }
        if ($request->filled('status_berkas')) {
            $filtered = $filtered->where('statusBerkas', $request->status_berkas);
        }
        if ($request->filled('status_daftar_ulang')) {
            $filtered = $filtered->where('statusDaftarUlang', $request->status_daftar_ulang);
        }
        if ($request->filled('pelunasan_tahap2')) {
            $filtered = $filtered->where('statusTahap2', $request->pelunasan_tahap2);
        }
        if ($request->filled('metode')) {
            $filtered = $filtered->where('metode', $request->metode);
        }

        // 7. Pagination manual untuk hasil koleksi terdekorasi
        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $filtered->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedItems = new LengthAwarePaginator(
            $currentItems,
            $filtered->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        // List Tahun Ajaran untuk Filter Dropdown
        $tahunAjaranList = TahunAjaran::orderBy('id_tahun_ajaran', 'desc')->get();
        $activeTahunAjaran = $request->filled('tahun_ajaran')
            ? TahunAjaran::find($request->tahun_ajaran)
            : ($tahunAjaranList->where('status', 'Berlangsung')->first() ?? $tahunAjaranList->first());

        return view('panitia.daftar_ulang.index', [
            'daftarUlang' => $paginatedItems,
            'summary' => $summary,
            'tabCounts' => $tabCounts,
            'activeTab' => $activeTab,
            'tahunAjaranList' => $tahunAjaranList,
            'activeTahunAjaran' => $activeTahunAjaran,
        ]);
    }

    /**
     * Tampilan detail lengkap administrasi daftar ulang calon siswa
     */
    public function show($id)
    {
        if (!Auth::check() || Auth::user()?->role !== 'Admin') {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        [$casis, $daftarUlang] = $this->resolveCasisAndDaftarUlang($id);

        if ($daftarUlang) {
            $pending = $daftarUlang->pembayaranDaftarUlang->whereIn('status_du', ['Menunggu', 'Pending'])->first();
            if ($pending && $pending->order_id_du) {
                $this->syncPaymentStatus($pending, $daftarUlang);
                $daftarUlang->load('pembayaranDaftarUlang');
            }
        }

        $item = $this->decorateCasisDaftarUlang($casis);

        return view('panitia.daftar_ulang.show', [
            'item' => $item,
            'casis' => $casis,
            'daftarUlang' => $item->daftarUlang,
            'riwayatPembayaran' => $item->riwayatPembayaran,
            'components' => $item->components,
        ]);
    }

    /**
     * Catat Setoran Pembayaran Tunai di Sekolah oleh Admin/Panitia
     */
    public function storePembayaranTunai(Request $request, $id)
    {
        if (!Auth::check() || Auth::user()?->role !== 'Admin') {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        [$casis, $daftarUlang] = $this->resolveCasisAndDaftarUlang($id, true);

        $item = $this->decorateCasisDaftarUlang($casis);
        $sisaSaatIni = $item->sisaBiaya;

        $validated = $request->validate([
            'jml_bayar_du' => ['required', 'numeric', 'min:1000', 'max:' . max(1000, $sisaSaatIni)],
            'tgl_bayar_du' => ['required', 'date'],
            'no_kuitansi' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ], [
            'jml_bayar_du.max' => 'Nominal pembayaran tidak boleh melebihi sisa tagihan saat ini (Rp ' . number_format($sisaSaatIni, 0, ',', '.') . ').',
            'jml_bayar_du.min' => 'Nominal pembayaran minimal Rp 1.000.',
        ]);

        $nominal = (float) $validated['jml_bayar_du'];
        $tglBayar = Carbon::parse($validated['tgl_bayar_du']);
        $adminUser = Auth::user();
        $adminName = $adminUser->nama ?? $adminUser->name ?? 'Admin PPDB';

        $noKuitansi = !empty($validated['no_kuitansi'])
            ? trim($validated['no_kuitansi'])
            : ('KW-DU-' . date('Ymd') . '-' . strtoupper(Str::random(5)));

        $orderId = 'DU-CASH-' . date('Ymd') . '-' . str_pad($casis->id_calon_siswa, 4, '0', STR_PAD_LEFT) . '-' . time();

        $totalSetelahSetoran = $item->totalTerbayar + $nominal;
        $jenisBayar = ($totalSetelahSetoran >= $item->totalBiaya) ? 'Lunas' : 'Cicil';

        DB::beginTransaction();
        try {
            // Buat record pembayaran tunai
            $pembayaran = PembayaranDaftarUlang::create([
                'id_daftar_ulang' => $daftarUlang->id_daftar_ulang,
                'order_id_du' => $orderId,
                'tgl_bayar_du' => $tglBayar,
                'jml_bayar_du' => $nominal,
                'jenis_bayar_du' => $jenisBayar,
                'metode_pembayaran' => 'Tunai',
                'status_du' => 'Lunas',
                'waktu_lunas' => $tglBayar,
                'no_kuitansi' => $noKuitansi,
                'keterangan' => $validated['keterangan'] ?? 'Pembayaran tunai di kasir sekolah',
                'dicatat_oleh' => $adminName,
            ]);

            // Jika cicilan pertama baru terpenuhi, tentukan otomatis jatuh tempo tahap 2 (+30 hari)
            if ($totalSetelahSetoran >= $item->minCicilan1 && !$daftarUlang->tgl_jatuh_tempo_du && $totalSetelahSetoran < $item->totalBiaya) {
                $daftarUlang->tgl_jatuh_tempo_du = Carbon::parse($tglBayar)->addDays(30);
            }

            // Evaluasi status daftar ulang
            $cicilan1Terpenuhi = ($totalSetelahSetoran >= $item->minCicilan1);
            $berkasDiterima = ($daftarUlang->status_berkas === 'Diterima');

            if ($cicilan1Terpenuhi && $berkasDiterima) {
                $daftarUlang->status_daftar_ulang = 'Selesai';
            } else {
                $daftarUlang->status_daftar_ulang = 'Menunggu Berkas';
            }

            $daftarUlang->save();

            DB::commit();

            return redirect()->back()->with('success', 'Pembayaran tunai sebesar Rp ' . number_format($nominal, 0, ',', '.') . ' berhasil dicatat (No. Kuitansi: ' . $noKuitansi . ').');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[Catat Pembayaran Tunai] Gagal: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mencatat pembayaran tunai: ' . $e->getMessage());
        }
    }

    /**
     * Terima / Perbarui Status Berkas Fisik Calon Siswa
     */
    public function updateBerkas(Request $request, $id)
    {
        if (!Auth::check() || Auth::user()?->role !== 'Admin') {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        [$casis, $daftarUlang] = $this->resolveCasisAndDaftarUlang($id, true);

        $validated = $request->validate([
            'status_berkas' => ['required', 'in:Menunggu Berkas,Diterima,Ditolak'],
            'jenis_berkas' => ['nullable', 'in:Asli,Fotokopi,Asli & Fotokopi'],
            'catatan_berkas' => ['nullable', 'string', 'max:500'],
        ]);

        $adminUser = Auth::user();
        $adminName = $adminUser->nama ?? $adminUser->name ?? 'Admin PPDB';

        DB::beginTransaction();
        try {
            $daftarUlang->status_berkas = $validated['status_berkas'];
            $daftarUlang->jenis_berkas = $validated['jenis_berkas'] ?? ($validated['status_berkas'] === 'Diterima' ? 'Asli' : null);
            $daftarUlang->catatan_berkas = $validated['catatan_berkas'] ?? null;

            if ($validated['status_berkas'] === 'Diterima') {
                $daftarUlang->tgl_berkas_diterima = $daftarUlang->tgl_berkas_diterima ?? now();
                $daftarUlang->penerima_berkas = $adminName;
            } else {
                $daftarUlang->tgl_berkas_diterima = null;
                $daftarUlang->penerima_berkas = null;
            }

            // Evaluasi status daftar ulang
            $item = $this->decorateCasisDaftarUlang($casis);
            $cicilan1Terpenuhi = ($item->totalTerbayar >= $item->minCicilan1);

            if ($validated['status_berkas'] === 'Diterima' && $cicilan1Terpenuhi) {
                $daftarUlang->status_daftar_ulang = 'Selesai';
            } else {
                $daftarUlang->status_daftar_ulang = 'Menunggu Berkas';
            }

            $daftarUlang->save();

            DB::commit();

            return redirect()->back()->with('success', 'Status penerimaan berkas fisik berhasil diperbarui menjadi: ' . $validated['status_berkas'] . '.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[Terima Berkas] Gagal: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui status berkas: ' . $e->getMessage());
        }
    }

    /**
     * Cetak Tanda Terima / Kuitansi Resmi Pembayaran Daftar Ulang
     */
    public function cetakKuitansi($id, $bayarId = null)
    {
        if (!Auth::check() || Auth::user()?->role !== 'Admin') {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        [$casis, $daftarUlang] = $this->resolveCasisAndDaftarUlang($id);

        if (!$daftarUlang) {
            abort(404, 'Belum ada data pembayaran daftar ulang.');
        }

        $item = $this->decorateCasisDaftarUlang($casis);

        if ($bayarId) {
            $pembayaran = $daftarUlang->pembayaranDaftarUlang()->where('id_bayar_daftar_ulang', $bayarId)->firstOrFail();
        } else {
            $pembayaran = $daftarUlang->pembayaranDaftarUlang()
                ->whereIn('status_du', ['Lunas', 'Diterima'])
                ->orderBy('tgl_bayar_du', 'desc')
                ->first();

            if (!$pembayaran) {
                abort(404, 'Tidak ditemukan riwayat pembayaran yang sah.');
            }
        }

        return view('panitia.daftar_ulang.kuitansi', [
            'casis' => $casis,
            'daftarUlang' => $daftarUlang,
            'pembayaran' => $pembayaran,
            'item' => $item,
        ]);
    }

    /**
     * Sinkronisasi status transaksi pembayaran daftar ulang dengan Midtrans Status API
     */
    public function syncPaymentStatus(PembayaranDaftarUlang $pembayaranDu, ?DaftarUlang $daftarUlang): void
    {
        if (!$pembayaranDu->order_id_du) {
            return;
        }

        try {
            $midtransStatus = $this->midtransService->getTransactionStatus($pembayaranDu->order_id_du);
            $transactionStatus = $midtransStatus['transaction_status'] ?? null;
            $fraudStatus = $midtransStatus['fraud_status'] ?? 'accept';
            $transactionId = $midtransStatus['transaction_id'] ?? null;
            $paymentType = $midtransStatus['payment_type'] ?? 'midtrans';

            $vaNumber = null;
            if (!empty($midtransStatus['va_numbers'][0]['va_number'])) {
                $vaNumber = $midtransStatus['va_numbers'][0]['bank'] . ' - ' . $midtransStatus['va_numbers'][0]['va_number'];
            } elseif (!empty($midtransStatus['permata_va_number'])) {
                $vaNumber = 'permata - ' . $midtransStatus['permata_va_number'];
            } elseif (!empty($midtransStatus['bill_key'])) {
                $vaNumber = 'mandiri - ' . $midtransStatus['bill_key'];
            }

            $appStatus = match ($transactionStatus) {
                'capture' => ($fraudStatus === 'challenge') ? 'Pending' : 'Lunas',
                'settlement' => 'Lunas',
                'pending' => 'Pending',
                'deny', 'cancel' => 'Gagal',
                'expire' => 'Kadaluarsa',
                default => 'Pending',
            };

            $statusDu = match ($appStatus) {
                'Lunas' => 'Lunas',
                'Pending' => 'Pending',
                'Kadaluarsa' => 'Kadaluarsa',
                'Gagal' => 'Ditolak',
                default => 'Pending',
            };

            if ($statusDu !== $pembayaranDu->status_du) {
                $updateData = [
                    'transaction_id_du' => $transactionId ?: $pembayaranDu->transaction_id_du,
                    'status_du' => $statusDu,
                    'metode_pembayaran' => $paymentType,
                ];

                if ($vaNumber) {
                    $updateData['va_number'] = $vaNumber;
                }

                $rawMidtransTime = $midtransStatus['settlement_time'] ?? $midtransStatus['transaction_time'] ?? null;
                $utcSettlementTime = parse_midtrans_time($rawMidtransTime) ?? now('UTC');

                if ($statusDu === 'Lunas') {
                    $updateData['waktu_lunas'] = $pembayaranDu->waktu_lunas ?? $utcSettlementTime;
                    $updateData['tgl_bayar_du'] = $updateData['waktu_lunas'];
                } elseif ($statusDu === 'Kadaluarsa') {
                    $updateData['waktu_kadaluarsa'] = now('UTC');
                }

                $pembayaranDu->update($updateData);

                if ($daftarUlang) {
                    $totalBiaya = (float) ($daftarUlang->calonSiswa?->tahunAjaran?->biaya_daftar_ulang ?? 1000000);
                    $totalLunas = $daftarUlang->pembayaranDaftarUlang()
                        ->whereIn('status_du', ['Lunas', 'Diterima'])
                        ->sum('jml_bayar_du');

                    $minCicilan1 = 500000;
                    if ($totalLunas >= $minCicilan1 && !$daftarUlang->tgl_jatuh_tempo_du && $totalLunas < $totalBiaya) {
                        $daftarUlang->tgl_jatuh_tempo_du = Carbon::parse($utcSettlementTime)->addDays(30);
                    }

                    if ($totalLunas >= $minCicilan1 && $daftarUlang->status_berkas === 'Diterima') {
                        $daftarUlang->status_daftar_ulang = 'Selesai';
                    }

                    $daftarUlang->save();
                }

                Log::info("[Admin AutoSync Midtrans DU] Order ID: {$pembayaranDu->order_id_du} diupdate menjadi {$statusDu}");
            }
        } catch (\Exception $e) {
            Log::info("[Admin AutoSync Midtrans DU] Gagal sinkron status: " . $e->getMessage());
        }
    }

    /**
     * Update status verifikasi pembayaran daftar ulang (Backward Compatibility)
     */
    public function update(Request $request, $id)
    {
        if (!Auth::check() || Auth::user()?->role !== 'Admin') {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        [$casis, $daftarUlang] = $this->resolveCasisAndDaftarUlang($id, true);

        $validated = $request->validate([
            'status_bayar' => 'required|in:Berhasil,Menunggu Konfirmasi,Gagal',
            'keterangan' => 'nullable|string',
        ]);

        $statusDu = match ($validated['status_bayar']) {
            'Berhasil' => 'Lunas',
            'Gagal' => 'Ditolak',
            default => 'Menunggu',
        };

        $latestPayment = $daftarUlang->pembayaranTerakhir;
        if ($latestPayment) {
            $latestPayment->update([
                'status_du' => $statusDu,
                'waktu_lunas' => ($statusDu === 'Lunas') ? now() : null,
            ]);
        }

        $item = $this->decorateCasisDaftarUlang($casis);
        if ($item->totalTerbayar >= $item->minCicilan1 && $daftarUlang->status_berkas === 'Diterima') {
            $daftarUlang->update(['status_daftar_ulang' => 'Selesai']);
        } else {
            $daftarUlang->update(['status_daftar_ulang' => 'Menunggu Berkas']);
        }

        return redirect()->route('admin.daftar_ulang.index')->with('success', 'Status verifikasi daftar ulang berhasil diperbarui.');
    }

    /**
     * Membangun query data laporan daftar ulang dengan filter dinamis
     */
    protected function buildLaporanDaftarUlangQuery(Request $request)
    {
        $query = DaftarUlang::with([
            'seleksi.pembayaran.calonSiswa.tahunAjaran',
            'pembayaranDaftarUlang'
        ]);

        // Filter Rentang Tanggal Daftar Ulang
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_daftar_ulang', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->where('tgl_daftar_ulang', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('tgl_daftar_ulang', '<=', $request->end_date);
        }

        // Filter Status Daftar Ulang
        if ($request->filled('status')) {
            $st = $request->status;
            if ($st === 'Berhasil') {
                $query->where(function($q) {
                    $q->whereIn('status_daftar_ulang', ['Berhasil', 'Lunas', 'Selesai'])
                      ->orWhereHas('pembayaranDaftarUlang', function($pq) {
                          $pq->whereIn('status_du', ['Lunas', 'Berhasil', 'Diterima']);
                      });
                });
            } elseif ($st === 'Menunggu Konfirmasi') {
                $query->where(function($q) {
                    $q->whereIn('status_daftar_ulang', ['Menunggu Konfirmasi', 'Menunggu', 'Pending'])
                      ->orWhereNull('status_daftar_ulang');
                });
            } elseif ($st === 'Gagal') {
                $query->whereIn('status_daftar_ulang', ['Gagal', 'Ditolak', 'Batal']);
            }
        }

        // Filter Status Berkas
        if ($request->filled('status_berkas')) {
            $sb = $request->status_berkas;
            if ($sb === 'Diterima') {
                $query->whereIn('status_berkas', ['Diterima', 'Sudah Diterima', 'Lengkap']);
            } elseif ($sb === 'Menunggu') {
                $query->where(function($q) {
                    $q->whereIn('status_berkas', ['Menunggu', 'Pending', 'Belum Lengkap'])
                      ->orWhereNull('status_berkas');
                });
            }
        }

        // Filter Tahun Ajaran
        if ($request->filled('id_tahun_ajaran')) {
            $query->whereHas('seleksi.pembayaran.calonSiswa', function ($q) use ($request) {
                $q->where('id_tahun_ajaran', $request->id_tahun_ajaran);
            });
        }

        // Filter Pencarian (Nama Calon Siswa, No. Registrasi)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('seleksi.pembayaran.calonSiswa', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_calon_siswa', 'like', "%{$search}%")
                        ->orWhere('no_registrasi', 'like', "%{$search}%");
                });
            });
        }

        return $query->orderBy('id_daftar_ulang', 'desc');
    }

    /**
     * Halaman laporan daftar ulang
     */
    public function laporanDaftarUlang(Request $request)
    {
        if (!Auth::check() || Auth::user()?->role !== 'Admin') {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        $daftarUlang = $this->buildLaporanDaftarUlangQuery($request)->get();

        return view('laporan.daftar-ulang.index', compact('daftarUlang'));
    }

    /**
     * Cetak laporan daftar ulang
     */
    public function cetak(Request $request)
    {
        if (!Auth::check() || Auth::user()?->role !== 'Admin') {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        $daftarUlang = $this->buildLaporanDaftarUlangQuery($request)->get();

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if ($startDate && $endDate) {
            $periodText = "Periode: " . date('d/m/Y', strtotime($startDate)) . " sampai " . date('d/m/Y', strtotime($endDate));
        } elseif ($startDate) {
            $periodText = "Periode: Mulai " . date('d/m/Y', strtotime($startDate));
        } elseif ($endDate) {
            $periodText = "Periode: Sampai " . date('d/m/Y', strtotime($endDate));
        } else {
            $periodText = "Periode: Seluruh Data";
        }

        $activeFilter = $request->only(['start_date', 'end_date', 'status', 'status_berkas', 'id_tahun_ajaran', 'search']);
        $filterTahunAjaran = !empty($activeFilter['id_tahun_ajaran']) ? \App\Models\TahunAjaran::find($activeFilter['id_tahun_ajaran']) : null;

        $namaKepalaSekolah = \App\Models\TahunAjaran::where('status', 'Berlangsung')->value('nama_kepala_sekolah')
            ?? config('sekolah.kepala_sekolah', 'Serni, S.Pd.K');
        $jabatanPetugas = 'Administrator';
        $namaPetugas = Auth::user()?->nama ?? (Auth::user()?->name ?? 'Administrator');

        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $now = \Carbon\Carbon::now();
        $tanggalCetak = $now->day . ' ' . $bulanIndo[$now->month] . ' ' . $now->year;
        $kotaSekolah = config('sekolah.kota', 'Rantai Damai');

        return view('laporan.daftar-ulang.cetak', compact(
            'daftarUlang', 'periodText', 'activeFilter', 'filterTahunAjaran',
            'namaKepalaSekolah', 'jabatanPetugas', 'namaPetugas', 'tanggalCetak', 'kotaSekolah'
        ));
    }

    /**
     * Helper Dekorasi Calon Siswa & Aturan Bisnis Daftar Ulang
     */
    protected function decorateCasisDaftarUlang(CalonSiswa $casis): object
    {
        $ta = $casis->tahunAjaran;
        $totalBiaya = (float) ($ta?->biaya_daftar_ulang ?? 1000000);
        $biayaBuku = (float) ($ta?->biaya_buku ?? 200000);
        $biayaSeragam = (float) ($ta?->biaya_seragam ?? 200000);
        $biayaPembangunan = (float) ($ta?->biaya_pembangunan ?? 600000);
        $dpPembangunan = 100000;
        $minCicilan1 = $biayaBuku + $biayaSeragam + $dpPembangunan; // Rp 500.000

        $du = $casis->seleksi?->daftarUlang;
        $payments = $du ? $du->pembayaranDaftarUlang : collect();
        $validPayments = $payments->whereIn('status_du', ['Lunas', 'Diterima']);

        $totalTerbayar = (float) $validPayments->sum('jml_bayar_du');
        $sisaBiaya = max(0, $totalBiaya - $totalTerbayar);
        $persenTerbayar = $totalBiaya > 0 ? min(100, round(($totalTerbayar / $totalBiaya) * 100)) : 0;

        // 1. Status Keuangan
        if ($totalTerbayar == 0) {
            $statusKeuangan = 'Belum Bayar';
        } elseif ($totalTerbayar >= $totalBiaya) {
            $statusKeuangan = 'Lunas';
        } elseif ($totalTerbayar >= $minCicilan1) {
            $statusKeuangan = 'Cicilan Awal Terpenuhi';
        } else {
            $statusKeuangan = 'Sebagian';
        }

        // 2. Status Berkas
        $statusBerkas = $du?->status_berkas ?? 'Menunggu Berkas';
        $jenisBerkas = $du?->jenis_berkas;

        // 3. Status Daftar Ulang
        $cicilan1Terpenuhi = ($totalTerbayar >= $minCicilan1);
        if ($du && $du->status_daftar_ulang === 'Batal') {
            $statusDaftarUlang = 'Batal';
        } elseif ($cicilan1Terpenuhi && $statusBerkas === 'Diterima') {
            $statusDaftarUlang = 'Selesai';
        } else {
            $statusDaftarUlang = 'Menunggu Berkas';
        }

        // 4. Tahap 2 & Jatuh Tempo
        $firstValidPayment = $validPayments->sortBy('tgl_bayar_du')->first();
        $waktuCicilan1 = $firstValidPayment ? ($firstValidPayment->waktu_lunas ?? $firstValidPayment->tgl_bayar_du) : null;

        $tglJatuhTempo = $du?->tgl_jatuh_tempo_du;
        if (!$tglJatuhTempo && $cicilan1Terpenuhi && $waktuCicilan1) {
            $tglJatuhTempo = Carbon::parse($waktuCicilan1)->addDays(30);
        }

        if ($sisaBiaya <= 0) {
            $statusTahap2 = 'Lunas';
        } elseif (!$cicilan1Terpenuhi) {
            $statusTahap2 = '-';
        } elseif (!$tglJatuhTempo) {
            $statusTahap2 = 'Belum Ditentukan';
        } else {
            $jt = Carbon::parse($tglJatuhTempo)->endOfDay();
            $today = now();
            if ($today->gt($jt)) {
                $statusTahap2 = 'Terlambat';
            } elseif ($today->isSameDay($jt)) {
                $statusTahap2 = 'Jatuh Tempo';
            } else {
                $statusTahap2 = 'Belum Jatuh Tempo';
            }
        }

        // 5. Metode & Tanggal Bayar Terakhir
        $lastPayment = $validPayments->sortByDesc('tgl_bayar_du')->first();
        if ($lastPayment) {
            $isTunai = strcasecmp($lastPayment->metode_pembayaran ?? '', 'tunai') === 0;
            $metode = $isTunai ? 'Tunai' : 'Gateway';
            $tglBayarTerakhir = $lastPayment->waktu_lunas ?? $lastPayment->tgl_bayar_du;
        } else {
            $metode = '-';
            $tglBayarTerakhir = null;
        }

        // 6. Waterfall Alokasi Komponen
        $bukuTerbayar = min($totalTerbayar, $biayaBuku);
        $bukuSisa = max(0, $biayaBuku - $bukuTerbayar);

        $remBuku = max(0, $totalTerbayar - $biayaBuku);
        $seragamTerbayar = min($remBuku, $biayaSeragam);
        $seragamSisa = max(0, $biayaSeragam - $seragamTerbayar);

        $remSeragam = max(0, $totalTerbayar - $biayaBuku - $biayaSeragam);
        $pembangunanTerbayar = min($remSeragam, $biayaPembangunan);
        $pembangunanSisa = max(0, $biayaPembangunan - $pembangunanTerbayar);

        return (object) [
            'id' => $casis->id_calon_siswa,
            'id_daftar_ulang' => $du?->id_daftar_ulang,
            'casis' => $casis,
            'daftarUlang' => $du,
            'totalBiaya' => $totalBiaya,
            'minCicilan1' => $minCicilan1,
            'totalTerbayar' => $totalTerbayar,
            'sisaBiaya' => $sisaBiaya,
            'persenTerbayar' => $persenTerbayar,
            'statusKeuangan' => $statusKeuangan,
            'statusBerkas' => $statusBerkas,
            'jenisBerkas' => $jenisBerkas,
            'statusDaftarUlang' => $statusDaftarUlang,
            'tglJatuhTempo' => $tglJatuhTempo,
            'statusTahap2' => $statusTahap2,
            'metode' => $metode,
            'tglBayarTerakhir' => $tglBayarTerakhir,
            'components' => [
                'buku' => ['nama' => 'Buku Paket Pelajaran & Modul', 'total' => $biayaBuku, 'terbayar' => $bukuTerbayar, 'sisa' => $bukuSisa],
                'seragam' => ['nama' => 'Seragam Sekolah & Atribut', 'total' => $biayaSeragam, 'terbayar' => $seragamTerbayar, 'sisa' => $seragamSisa],
                'pembangunan' => ['nama' => 'Uang Pembangunan & Sarana', 'total' => $biayaPembangunan, 'terbayar' => $pembangunanTerbayar, 'sisa' => $pembangunanSisa],
            ],
            'riwayatPembayaran' => $payments->sortByDesc('tgl_bayar_du'),
        ];
    }

    /**
     * Resolve Calon Siswa & Record Daftar Ulang secara andal
     */
    protected function resolveCasisAndDaftarUlang($id, bool $createDuIfNotExists = false): array
    {
        // Cari berdasarkan id_calon_siswa terlebih dahulu
        $casis = CalonSiswa::with(['tahunAjaran', 'seleksi.daftarUlang.pembayaranDaftarUlang'])->find($id);

        // Jika tidak ditemukan, coba cari berdasarkan id_daftar_ulang
        if (!$casis) {
            $du = DaftarUlang::with(['seleksi.pembayaran.calonSiswa.tahunAjaran', 'pembayaranDaftarUlang'])->find($id);
            if ($du) {
                $casis = $du->calonSiswa;
                return [$casis, $du];
            }
        }

        if (!$casis) {
            abort(404, 'Data calon siswa tidak ditemukan.');
        }

        $du = $casis->seleksi?->daftarUlang;

        if (!$du && $createDuIfNotExists && $casis->seleksi) {
            $du = DaftarUlang::firstOrCreate(
                ['id_seleksi' => $casis->seleksi->id_seleksi],
                [
                    'tgl_daftar_ulang' => now()->toDateString(),
                    'status_daftar_ulang' => 'Menunggu Berkas',
                    'status_berkas' => 'Menunggu Berkas',
                ]
            );
            $casis->load('seleksi.daftarUlang.pembayaranDaftarUlang');
        }

        return [$casis, $du];
    }
}

