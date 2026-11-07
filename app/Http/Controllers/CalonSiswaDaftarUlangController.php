<?php

namespace App\Http\Controllers;

use App\Models\CalonSiswa;
use App\Models\DaftarUlang;
use App\Models\PembayaranDaftarUlang;
use App\Models\Seleksi;
use App\Models\TahunAjaran;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CalonSiswaDaftarUlangController extends Controller
{
    protected MidtransService $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Tampilan form daftar ulang dan rincian biaya untuk calon siswa
     */
    public function show()
    {
        $clientKey = $this->midtransService->getClientKey();
        $snapJsUrl = $this->midtransService->getSnapJsUrl();

        $user = Auth::user();
        $casis = $user->calonSiswa;
        $archivedCasis = $casis ? null : $user->calonSiswa()->onlyTrashed()->first();
        $isArchived = !empty($archivedCasis);

        $tahunAjar = $casis?->tahunAjaran ?? $archivedCasis?->tahunAjaran ?? TahunAjaran::where('status', 'Berlangsung')->first();
        $totalBiaya = (float) ($tahunAjar?->biaya_daftar_ulang ?? 5000000);
        if ($totalBiaya <= 0) {
            $totalBiaya = 5000000;
        }

        // Komponen rincian biaya resmi untuk tampilan (diambil dari pengaturan tahun ajaran)
        $biayaComponents = $tahunAjar?->biaya_components ?? [
            (object) ['nama_biaya' => 'Uang Pembangunan & Sarana', 'nominal' => $totalBiaya * 0.60, 'keterangan' => 'Fasilitas & sarana pembelajaran sekolah'],
            (object) ['nama_biaya' => 'Buku Paket Pelajaran & Modul', 'nominal' => $totalBiaya * 0.20, 'keterangan' => 'Buku paket & modul belajar 1 tahun ajaran'],
            (object) ['nama_biaya' => 'Seragam Sekolah & Atribut', 'nominal' => $totalBiaya * 0.20, 'keterangan' => 'Seragam lengkap beserta kelengkapan atribut'],
        ];

        if (!$casis) {
            $pesanError = $isArchived
                ? 'Data pendaftaran Anda sedang diarsipkan. Silakan hubungi panitia.'
                : 'Anda belum melakukan pendaftaran. Silakan lengkapi formulir pendaftaran terlebih dahulu.';

            return view('casis.daftar_ulang.show', compact('clientKey', 'snapJsUrl', 'totalBiaya', 'biayaComponents', 'tahunAjar', 'isArchived', 'archivedCasis') + [
                'error' => $pesanError
            ]);
        }

        $seleksi = $casis->seleksi;

        if (!$seleksi || !$seleksi->isDiumumkan() || !in_array($seleksi->hasil_seleksi, ['Lulus', 'Lolos'])) {
            return view('casis.daftar_ulang.show', compact('clientKey', 'snapJsUrl', 'seleksi', 'casis', 'totalBiaya', 'biayaComponents', 'tahunAjar') + [
                'error' => 'Maaf, Anda belum dapat melakukan daftar ulang. Daftar ulang hanya bisa dilakukan setelah Anda dinyatakan Lulus seleksi penerimaan siswa baru dan hasil seleksi telah resmi dipublikasikan.'
            ]);
        }

        $daftarUlang = DaftarUlang::with('pembayaranDaftarUlang')
            ->where('id_seleksi', $seleksi->id_seleksi)
            ->first();

        // Hitung riwayat pembayaran jika sudah ada daftar ulang
        $riwayatPembayaran = $daftarUlang ? $daftarUlang->pembayaranDaftarUlang : collect();
        $pembayaranPending = $riwayatPembayaran->whereIn('status_du', ['Menunggu', 'Pending'])->first();

        // Auto-sync status pembayaran dengan Midtrans Status API jika ada transaksi pending
        if ($pembayaranPending && $pembayaranPending->order_id_du) {
            $this->syncPaymentStatusWithMidtrans($pembayaranPending, $daftarUlang, $totalBiaya);
            $daftarUlang->load('pembayaranDaftarUlang');
            $riwayatPembayaran = $daftarUlang->pembayaranDaftarUlang;
            $pembayaranPending = $riwayatPembayaran->whereIn('status_du', ['Menunggu', 'Pending'])->first();
        }

        $totalSudahDibayar = $riwayatPembayaran->whereIn('status_du', ['Lunas', 'Diterima'])->sum('jml_bayar_du');
        $sisaBiaya = max(0, $totalBiaya - $totalSudahDibayar);
        $sisaTagihan = $sisaBiaya;
        $minDp = min(500000, $sisaTagihan > 0 ? $sisaTagihan : 500000);

        $clientKey = $this->midtransService->getClientKey();
        $snapJsUrl = $this->midtransService->getSnapJsUrl();

        return view('casis.daftar_ulang.show', compact(
            'casis',
            'seleksi',
            'daftarUlang',
            'biayaComponents',
            'totalBiaya',
            'riwayatPembayaran',
            'totalSudahDibayar',
            'sisaBiaya',
            'sisaTagihan',
            'minDp',
            'pembayaranPending',
            'clientKey',
            'snapJsUrl'
        ));
    }

    /**
     * Generate Midtrans Snap Token untuk Pembayaran Daftar Ulang
     */
    public function createSnapToken(Request $request)
    {
        $user = Auth::user();
        $casis = $user->calonSiswa;

        if (!$casis) {
            if ($user->calonSiswa()->onlyTrashed()->exists()) {
                return response()->json(['success' => false, 'message' => 'Data pendaftaran Anda sedang diarsipkan. Silakan hubungi panitia.'], 403);
            }
            return response()->json(['success' => false, 'message' => 'Data calon siswa tidak ditemukan.'], 404);
        }

        $seleksi = $casis->seleksi;
        if (!$seleksi || !$seleksi->isDiumumkan() || !in_array($seleksi->hasil_seleksi, ['Lulus', 'Lolos'])) {
            return response()->json(['success' => false, 'message' => 'Hasil seleksi belum diumumkan atau Anda belum dinyatakan lulus seleksi.'], 403);
        }

        $tahunAjar = $casis->tahunAjaran ?? TahunAjaran::where('status', 'Berlangsung')->first();
        $totalBiaya = (float) ($tahunAjar?->biaya_daftar_ulang ?? 5000000);
        if ($totalBiaya <= 0) {
            $totalBiaya = 5000000;
        }

        // Ambil atau buat record tb_daftar_ulang (relasi 1:1 ke tb_seleksi)
        $daftarUlang = DaftarUlang::firstOrCreate(
            ['id_seleksi' => $seleksi->id_seleksi],
            [
                'tgl_daftar_ulang' => now()->toDateString(),
                'status_daftar_ulang' => 'Menunggu',
            ]
        );

        $riwayatPembayaran = $daftarUlang->pembayaranDaftarUlang;
        $totalSudahDibayar = $riwayatPembayaran->whereIn('status_du', ['Lunas', 'Diterima'])->sum('jml_bayar_du');
        $sisaBiaya = max(0, $totalBiaya - $totalSudahDibayar);

        if ($sisaBiaya <= 0) {
            return response()->json(['success' => false, 'message' => 'Biaya daftar ulang sudah lunas penuh.'], 400);
        }

        $metodePembayaran = $request->input('metode_pembayaran', 'Lunas'); // 'DP 50%' atau 'Lunas'

        if ($metodePembayaran === 'DP 50%' && $totalSudahDibayar == 0) {
            $nominalBayar = (int) round($totalBiaya * 0.5);
            $jenisBayar = 'Cicil';
            $namaItem = 'DP Daftar Ulang PPDB (50%)';
        } else {
            $nominalBayar = (int) round($sisaBiaya);
            $jenisBayar = 'Lunas';
            $namaItem = ($totalSudahDibayar > 0) ? 'Pelunasan Sisa Biaya Daftar Ulang' : 'Pelunasan Penuh Biaya Daftar Ulang';
        }

        try {
            DB::beginTransaction();

            $orderIdDu = 'DU-' . date('Ymd') . '-' . str_pad($daftarUlang->id_daftar_ulang, 4, '0', STR_PAD_LEFT) . '-' . time();

            $snapPayload = [
                'transaction_details' => [
                    'order_id' => $orderIdDu,
                    'gross_amount' => $nominalBayar,
                ],
                'customer_details' => [
                    'first_name' => $casis->nama_calon_siswa,
                    'email' => $user->email ?: ($user->username . '@ppdb.sch.id'),
                    'phone' => $casis->no_hp_calon_siswa ?: ($casis->no_hp_orang_tua ?: '081234567890'),
                ],
                'item_details' => [
                    [
                        'id' => 'BIAYA-DU-' . $daftarUlang->id_daftar_ulang,
                        'price' => $nominalBayar,
                        'quantity' => 1,
                        'name' => $namaItem,
                    ],
                ],
            ];

            $midtransRes = $this->midtransService->createSnapTransaction($snapPayload);
            $snapToken = $midtransRes['token'];
            $paymentUrl = $midtransRes['redirect_url'];

            // Cek apakah ada record pembayaran DU yang sedang pending/menunggu
            $existingPayment = PembayaranDaftarUlang::where('id_daftar_ulang', $daftarUlang->id_daftar_ulang)
                ->whereIn('status_du', ['Menunggu', 'Pending', 'Kadaluarsa'])
                ->first();

            if ($existingPayment) {
                $existingPayment->update([
                    'order_id_du' => $orderIdDu,
                    'tgl_bayar_du' => now(),
                    'jml_bayar_du' => $nominalBayar,
                    'jenis_bayar_du' => $jenisBayar,
                    'snap_token' => $snapToken,
                    'payment_url' => $paymentUrl,
                    'status_du' => 'Pending',
                    'waktu_kadaluarsa' => now()->addDay(),
                    'waktu_lunas' => null,
                ]);
            } else {
                PembayaranDaftarUlang::create([
                    'id_daftar_ulang' => $daftarUlang->id_daftar_ulang,
                    'order_id_du' => $orderIdDu,
                    'tgl_bayar_du' => now(),
                    'jml_bayar_du' => $nominalBayar,
                    'jenis_bayar_du' => $jenisBayar,
                    'snap_token' => $snapToken,
                    'payment_url' => $paymentUrl,
                    'status_du' => 'Pending',
                    'waktu_kadaluarsa' => now()->addDay(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $orderIdDu,
                'gross_amount' => $nominalBayar,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error createSnapToken daftar ulang: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat sesi pembayaran Midtrans: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Konfirmasi pembayaran daftar ulang oleh calon siswa
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $casis = $user->calonSiswa;

        if (!$casis) {
            if ($user->calonSiswa()->onlyTrashed()->exists()) {
                return redirect()->back()->with('error', 'Data pendaftaran Anda sedang diarsipkan. Silakan hubungi panitia.');
            }
            return redirect()->back()->with('error', 'Data calon siswa tidak ditemukan.');
        }

        $seleksi = $casis->seleksi;
        if (!$seleksi || !$seleksi->isDiumumkan() || !in_array($seleksi->hasil_seleksi, ['Lulus', 'Lolos'])) {
            return redirect()->back()->with('error', 'Hasil seleksi belum diumumkan atau Anda belum dinyatakan lulus seleksi.');
        }

        $tahunAjar = $casis->tahunAjaran ?? TahunAjaran::where('status', 'Berlangsung')->first();
        $totalBiaya = (float) ($tahunAjar?->biaya_daftar_ulang ?? 5000000);
        if ($totalBiaya <= 0) {
            $totalBiaya = 5000000;
        }

        // Hapus pemisah ribuan
        $jumlahBayar = (float) str_replace(['.', ','], ['', '.'], $request->jumlah_bayar);

        $request->validate([
            'metode_pembayaran' => 'required|in:DP 50%,Lunas',
            'jumlah_bayar' => [
                'required',
                function ($attribute, $value, $fail) use ($totalBiaya, $jumlahBayar) {
                    if ($jumlahBayar < ($totalBiaya * 0.49)) {
                        $fail('Pembayaran minimum untuk cicilan (DP) adalah 50% dari total biaya (Rp ' . number_format($totalBiaya * 0.5, 0, ',', '.') . ').');
                    } elseif ($jumlahBayar > $totalBiaya) {
                        $fail('Jumlah pembayaran tidak boleh melebihi total biaya daftar ulang (Rp ' . number_format($totalBiaya, 0, ',', '.') . ').');
                    }
                },
            ],
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'bukti_pembayaran.required' => 'Bukti pembayaran wajib diunggah.',
            'bukti_pembayaran.image' => 'File bukti harus berupa gambar.',
            'bukti_pembayaran.max' => 'Ukuran file maksimal 2MB.',
        ]);

        try {
            DB::beginTransaction();

            // Ambil atau buat record tb_daftar_ulang (relasi 1:1 ke tb_seleksi)
            $daftarUlang = DaftarUlang::firstOrCreate(
                ['id_seleksi' => $seleksi->id_seleksi],
                [
                    'tgl_daftar_ulang' => now()->toDateString(),
                    'status_daftar_ulang' => 'Menunggu',
                ]
            );

            // Simpan file bukti transfer
            $file = $request->file('bukti_pembayaran');
            $fileName = 'du_' . $daftarUlang->id_daftar_ulang . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/daftar_ulang', $fileName);

            $jenisBayar = ($request->metode_pembayaran === 'DP 50%') ? 'Cicil' : 'Lunas';
            $orderIdDu = 'DU-' . date('Ymd') . '-' . str_pad($daftarUlang->id_daftar_ulang, 4, '0', STR_PAD_LEFT) . '-' . time();

            // Cek apakah ada pembayaran daftar ulang sebelumnya yang berstatus 'Menunggu' / 'Ditolak'
            $existingPayment = PembayaranDaftarUlang::where('id_daftar_ulang', $daftarUlang->id_daftar_ulang)
                ->whereIn('status_du', ['Menunggu', 'Pending', 'Ditolak', 'Kadaluarsa'])
                ->first();

            if ($existingPayment) {
                // Hapus bukti lama
                if ($existingPayment->bukti_bayar_du && Storage::disk('public')->exists('daftar_ulang/' . $existingPayment->bukti_bayar_du)) {
                    Storage::disk('public')->delete('daftar_ulang/' . $existingPayment->bukti_bayar_du);
                }

                $existingPayment->update([
                    'order_id_du' => $orderIdDu,
                    'tgl_bayar_du' => now(),
                    'jml_bayar_du' => $jumlahBayar,
                    'jenis_bayar_du' => $jenisBayar,
                    'metode_pembayaran' => 'Transfer Bank',
                    'bukti_bayar_du' => $fileName,
                    'status_du' => 'Menunggu',
                    'waktu_kadaluarsa' => null,
                    'waktu_lunas' => null,
                ]);
            } else {
                PembayaranDaftarUlang::create([
                    'id_daftar_ulang' => $daftarUlang->id_daftar_ulang,
                    'order_id_du' => $orderIdDu,
                    'tgl_bayar_du' => now(),
                    'jml_bayar_du' => $jumlahBayar,
                    'jenis_bayar_du' => $jenisBayar,
                    'metode_pembayaran' => 'Transfer Bank',
                    'bukti_bayar_du' => $fileName,
                    'status_du' => 'Menunggu',
                ]);
            }

            DB::commit();

            return redirect()->route('calon_siswa.daftar_ulang.show')->with('success', 'Bukti pembayaran daftar ulang berhasil diunggah. Mohon tunggu verifikasi dari panitia.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error daftar ulang: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses data: ' . $e->getMessage());
        }
    }

    /**
     * Cetak bukti pendaftaran ulang resmi
     */
    public function print()
    {
        $casis = auth()->user()->calonSiswa;
        if (!$casis) {
            if ($user->calonSiswa()->onlyTrashed()->exists()) {
                return redirect()->back()->with('error', 'Data pendaftaran Anda sedang diarsipkan. Silakan hubungi panitia.');
            }
            return redirect()->back()->with('error', 'Data calon siswa tidak ditemukan.');
        }

        $seleksi = $casis->seleksi;
        if (!$seleksi || !$seleksi->isDiumumkan()) {
            return redirect()->back()->with('error', 'Pengumuman seleksi belum resmi dipublikasikan.');
        }

        $daftarUlang = DaftarUlang::with(['pembayaranDaftarUlang'])
            ->where('id_seleksi', $seleksi->id_seleksi)
            ->firstOrFail();

        $tahunAjar = $casis->tahunAjaran ?? TahunAjaran::where('status', 'Berlangsung')->first();

        return view('casis.daftar_ulang.print', compact('daftarUlang', 'casis', 'seleksi', 'tahunAjar'));
    }

    /**
     * Sinkronisasi status transaksi pembayaran daftar ulang dengan Midtrans Status API
     */
    protected function syncPaymentStatusWithMidtrans(PembayaranDaftarUlang $pembayaranDu, ?DaftarUlang $daftarUlang, float $totalBiaya): void
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
                    $totalLunas = $daftarUlang->pembayaranDaftarUlang()
                        ->whereIn('status_du', ['Lunas', 'Diterima'])
                        ->sum('jml_bayar_du');

                    if ($totalLunas >= $totalBiaya) {
                        $daftarUlang->update(['status_daftar_ulang' => 'Selesai']);
                    }
                }

                Log::info("[AutoSync Midtrans DU] Order ID: {$pembayaranDu->order_id_du} diupdate menjadi {$statusDu}");
            }
        } catch (\Exception $e) {
            Log::info("[AutoSync Midtrans DU] Gagal sinkron status: " . $e->getMessage());
        }
    }
}
