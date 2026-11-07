<?php

namespace App\Http\Controllers;

use App\Models\CalonSiswa;
use App\Models\DaftarUlang;
use App\Models\Pembayaran;
use App\Models\PembayaranDaftarUlang;
use App\Models\Seleksi;
use App\Models\TahunAjaran;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BerandaController extends Controller
{
    /**
     * Dashboard Utama / Admin
     */
    public function index()
    {
        $tahunAjaranBerlangsung = TahunAjaran::where('status', 'Berlangsung')->first()
            ?? TahunAjaran::orderBy('id_tahun_ajaran', 'desc')->first();

        $taId = $tahunAjaranBerlangsung?->id_tahun_ajaran;

        $pendaftarQuery = CalonSiswa::query();
        if ($taId) {
            $pendaftarQuery->where('id_tahun_ajaran', $taId);
        }
        $totalPendaftar = $pendaftarQuery->count();

        // Diterima (Lulus Seleksi yang sudah resmi diumumkan)
        $seleksiQuery = Seleksi::whereNotNull('diumumkan_pada');
        if ($taId) {
            $seleksiQuery->whereHas('pembayaran.calonSiswa', function ($q) use ($taId) {
                $q->whereNull('tb_calon_siswa.deleted_at')
                  ->where('id_tahun_ajaran', $taId);
            });
        } else {
            $seleksiQuery->whereHas('pembayaran.calonSiswa', function ($q) {
                $q->whereNull('tb_calon_siswa.deleted_at');
            });
        }
        $totalDiterima = (clone $seleksiQuery)->whereIn('hasil_seleksi', ['Lulus', 'Lolos'])->count();
        $totalDitolak = (clone $seleksiQuery)->whereIn('hasil_seleksi', ['Tidak Lulus', 'Tidak Lolos', 'Ditolak'])->count();

        $tahunAjaranString = $tahunAjaranBerlangsung?->tahun_ajaran ?? 'Tidak ada data tahun ajaran';
        $kuotaTotal = $tahunAjaranBerlangsung?->kuota ?? 0;
        $kuotaTerisi = $totalDiterima;
        $kuotaTersisa = max(0, $kuotaTotal - $kuotaTerisi);

        return view('beranda', compact(
            'totalPendaftar',
            'totalDiterima',
            'totalDitolak',
            'tahunAjaranString',
            'kuotaTotal',
            'kuotaTerisi',
            'kuotaTersisa'
        ));
    }

    /**
     * Dashboard Calon Siswa
     */
    public function berandacasis()
    {
        $user = Auth::user();
        $casis = $user?->calonSiswa;
        $archivedCasis = $casis ? null : $user?->calonSiswa()->onlyTrashed()->with('tahunAjaran')->first();
        $isArchived = !empty($archivedCasis);

        $tahunajar = $casis?->tahunAjaran
            ?? $archivedCasis?->tahunAjaran
            ?? TahunAjaran::where('status', 'Berlangsung')->first()
            ?? TahunAjaran::orderBy('id_tahun_ajaran', 'desc')->first();

        $aktivTahunAjaran = $tahunajar;

        if ($casis) {
            $this->syncPendingPayments($casis, $tahunajar);
        }

        return view('berandacasis', compact('tahunajar', 'aktivTahunAjaran', 'casis', 'archivedCasis', 'isArchived'));
    }

    /**
     * Auto-sync status pembayaran pending (Daftar Ulang) dari Midtrans Status API
     */
    protected function syncPendingPayments(CalonSiswa $casis, ?TahunAjaran $tahunajar): void
    {
        $daftarUlang = $casis->seleksi?->daftarUlang;
        if (!$daftarUlang) {
            return;
        }

        $pendingDu = $daftarUlang->pembayaranDaftarUlang()
            ->whereIn('status_du', ['Menunggu', 'Pending'])
            ->whereNotNull('order_id_du')
            ->get();

        if ($pendingDu->isEmpty()) {
            return;
        }

        $midtransService = app(MidtransService::class);
        $totalBiayaDu = (float) ($tahunajar?->biaya_daftar_ulang ?? 1000000);

        foreach ($pendingDu as $pembayaranDu) {
            try {
                $midtransStatus = $midtransService->getTransactionStatus($pembayaranDu->order_id_du);
                $transactionStatus = $midtransStatus['transaction_status'] ?? null;
                $fraudStatus = $midtransStatus['fraud_status'] ?? 'accept';
                $transactionId = $midtransStatus['transaction_id'] ?? null;
                $paymentType = $midtransStatus['payment_type'] ?? 'midtrans';

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

                    $rawMidtransTime = $midtransStatus['settlement_time'] ?? $midtransStatus['transaction_time'] ?? null;
                    $utcSettlementTime = parse_midtrans_time($rawMidtransTime) ?? now('UTC');

                    if ($statusDu === 'Lunas') {
                        $updateData['waktu_lunas'] = $pembayaranDu->waktu_lunas ?? $utcSettlementTime;
                        $updateData['tgl_bayar_du'] = $updateData['waktu_lunas'];
                    } elseif ($statusDu === 'Kadaluarsa') {
                        $updateData['waktu_kadaluarsa'] = now('UTC');
                    }

                    $pembayaranDu->update($updateData);

                    $totalLunas = $daftarUlang->pembayaranDaftarUlang()
                        ->whereIn('status_du', ['Lunas', 'Diterima'])
                        ->sum('jml_bayar_du');

                    if ($totalLunas >= $totalBiayaDu) {
                        $daftarUlang->update(['status_daftar_ulang' => 'Selesai']);
                    }

                    Log::info("[Beranda AutoSync DU] Order ID: {$pembayaranDu->order_id_du} diupdate menjadi {$statusDu}");
                }
            } catch (\Exception $e) {
                Log::info("[Beranda AutoSync DU] " . $e->getMessage());
            }
        }
    }

    /**
     * Dashboard Panitia PPDB
     */
    public function berandapanitia()
    {
        $tahunAjaranBerlangsung = TahunAjaran::where('status', 'Berlangsung')->first()
            ?? TahunAjaran::orderBy('id_tahun_ajaran', 'desc')->first();

        $taId = $tahunAjaranBerlangsung?->id_tahun_ajaran;

        $pendaftarQuery = CalonSiswa::query();
        if ($taId) {
            $pendaftarQuery->where('id_tahun_ajaran', $taId);
        }
        $totalPendaftar = $pendaftarQuery->count();

        $seleksiQuery = Seleksi::query();
        if ($taId) {
            $seleksiQuery->whereHas('pembayaran.calonSiswa', function ($q) use ($taId) {
                $q->whereNull('tb_calon_siswa.deleted_at')
                  ->where('id_tahun_ajaran', $taId);
            });
        } else {
            $seleksiQuery->whereHas('pembayaran.calonSiswa', function ($q) {
                $q->whereNull('tb_calon_siswa.deleted_at');
            });
        }
        $totalDiterima = (clone $seleksiQuery)->whereIn('hasil_seleksi', ['Lulus', 'Lolos'])->count();
        $totalDitolak = (clone $seleksiQuery)->whereIn('hasil_seleksi', ['Tidak Lulus', 'Tidak Lolos', 'Ditolak'])->count();

        $tahunAjaranString = $tahunAjaranBerlangsung?->tahun_ajaran ?? 'Tidak ada data tahun ajaran';
        $kuotaTotal = $tahunAjaranBerlangsung?->kuota ?? 0;
        $kuotaTerisi = $totalDiterima;
        $kuotaTersisa = max(0, $kuotaTotal - $kuotaTerisi);

        // Top 5 Calon Siswa dengan Nilai Tertinggi yang Lulus
        $casisTertinggi = Seleksi::whereIn('hasil_seleksi', ['Lulus', 'Lolos'])
            ->whereHas('pembayaran.calonSiswa', function ($sq) use ($taId) {
                $sq->whereNull('tb_calon_siswa.deleted_at');
                if ($taId) {
                    $sq->where('id_tahun_ajaran', $taId);
                }
            })
            ->orderBy('total_nilai', 'desc')
            ->with('pembayaran.calonSiswa')
            ->take(5)
            ->get();

        return view('berandapanitia', compact(
            'totalPendaftar',
            'totalDiterima',
            'totalDitolak',
            'casisTertinggi',
            'tahunAjaranString',
            'kuotaTotal',
            'kuotaTerisi',
            'kuotaTersisa'
        ));
    }
}
