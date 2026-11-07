<?php

namespace App\Http\Controllers;

use App\Models\CalonSiswa;
use App\Models\Pembayaran;
use App\Models\TahunAjaran;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    protected MidtransService $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }
    /**
     * Tampilkan data pembayaran untuk Admin
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $statusFilter = $request->input('status');
        $metodeFilter = $request->input('metode');
        $tglAwal = $request->input('tgl_awal');
        $tglAkhir = $request->input('tgl_akhir');
        $tahunAjaranFilter = $request->input('tahun_ajaran');

        // Whitelist status pembayaran
        $allowedStatuses = ['Lunas', 'Pending', 'Menunggu', 'Kadaluarsa', 'Gagal'];
        if (!in_array($statusFilter, $allowedStatuses)) {
            $statusFilter = null;
        }

        $query = Pembayaran::with(['calonSiswa' => function ($q) {
            $q->withTrashed()->with('tahunAjaran');
        }]);

        if (!empty($search)) {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('order_id', 'like', "%{$escaped}%")
                  ->orWhereHas('calonSiswa', function ($sub) use ($escaped) {
                      $sub->where('nama_calon_siswa', 'like', "%{$escaped}%")
                          ->orWhere('no_registrasi', 'like', "%{$escaped}%");
                  });
            });
        }

        if (!empty($statusFilter)) {
            if ($statusFilter === 'Lunas') {
                $query->whereIn('status_pembayaran', ['Lunas', 'Diterima']);
            } elseif ($statusFilter === 'Pending') {
                $query->whereIn('status_pembayaran', ['Pending', 'Menunggu']);
            } elseif ($statusFilter === 'Gagal') {
                $query->whereIn('status_pembayaran', ['Gagal', 'Ditolak']);
            } else {
                $query->where('status_pembayaran', $statusFilter);
            }
        }

        if (!empty($metodeFilter)) {
            $query->where(function ($q) use ($metodeFilter) {
                $q->where('metode_pembayaran', $metodeFilter)
                  ->orWhere('channel', $metodeFilter);
            });
        }

        if (!empty($tglAwal)) {
            $query->whereDate(DB::raw('COALESCE(waktu_dibuat, tgl_pembayaran)'), '>=', $tglAwal);
        }

        if (!empty($tglAkhir)) {
            $query->whereDate(DB::raw('COALESCE(waktu_dibuat, tgl_pembayaran)'), '<=', $tglAkhir);
        }

        if (!empty($tahunAjaranFilter)) {
            $query->whereHas('calonSiswa', function ($sub) use ($tahunAjaranFilter) {
                $sub->where('id_tahun_ajaran', $tahunAjaranFilter);
            });
        }

        $data = $query->orderBy('id_pembayaran', 'desc')->paginate(10)->withQueryString();

        // 1 Query agregat statistik pembayaran efisien (mengikuti filter tahun ajaran bila dipilih)
        $statsQuery = DB::table('tb_pembayaran')
            ->leftJoin('tb_calon_siswa', 'tb_pembayaran.id_calon_siswa', '=', 'tb_calon_siswa.id_calon_siswa');

        if (!empty($tahunAjaranFilter)) {
            $statsQuery->where('tb_calon_siswa.id_tahun_ajaran', $tahunAjaranFilter);
        }

        $twentyFourHoursAgo = now('UTC')->subHours(24);
        $stats = $statsQuery->selectRaw("
            COUNT(*) as total_semua,
            COALESCE(SUM(jml_pembayaran), 0) as nominal_semua,
            COUNT(CASE WHEN status_pembayaran IN ('Lunas', 'Diterima') THEN 1 END) as total_lunas,
            COALESCE(SUM(CASE WHEN status_pembayaran IN ('Lunas', 'Diterima') THEN jml_pembayaran ELSE 0 END), 0) as nominal_lunas,
            COUNT(CASE WHEN status_pembayaran IN ('Pending', 'Menunggu') THEN 1 END) as total_menunggu,
            COALESCE(SUM(CASE WHEN status_pembayaran IN ('Pending', 'Menunggu') THEN jml_pembayaran ELSE 0 END), 0) as nominal_menunggu,
            COUNT(CASE WHEN status_pembayaran IN ('Kadaluarsa', 'Gagal', 'Ditolak') THEN 1 END) as total_gagal,
            COALESCE(SUM(CASE WHEN status_pembayaran IN ('Kadaluarsa', 'Gagal', 'Ditolak') THEN jml_pembayaran ELSE 0 END), 0) as nominal_gagal,
            COUNT(CASE WHEN (status_pembayaran IN ('Pending', 'Menunggu') AND COALESCE(waktu_dibuat, tgl_pembayaran) <= ?) OR status_pembayaran IN ('Gagal', 'Ditolak') THEN 1 END) as total_perlu_perhatian
        ", [$twentyFourHoursAgo])->first();

        $tahunAjaranList = TahunAjaran::orderBy('id_tahun_ajaran', 'desc')->get();

        return view('pembayaran.index', compact(
            'data',
            'search',
            'statusFilter',
            'metodeFilter',
            'tglAwal',
            'tglAkhir',
            'tahunAjaranFilter',
            'stats',
            'tahunAjaranList'
        ));
    }

    /**
     * Halaman informasi pembayaran untuk Calon Siswa / Orang Tua
     */
    public function infobayar()
    {
        $user = Auth::user();
        $casis = $user->calonSiswa;
        $archivedCasis = $casis ? null : $user->calonSiswa()->onlyTrashed()->first();
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

        return view('pembayaran.infobayar', compact('user', 'casis', 'archivedCasis', 'isArchived', 'kuotaTersedia', 'pesanKuota', 'tahunAjarBerlangsung'));
    }

    /**
     * Detail tagihan untuk verifikasi oleh Admin
     */
    public function tagihan(Request $request, $id)
    {
        $data = Pembayaran::with(['calonSiswa'])->where('id_pembayaran', $id)->firstOrFail();
        return view('pembayaran.tagihan', compact('data'));
    }

    /**
     * Proses perubahan status pembayaran oleh Admin
     */
    public function proses(Request $request, $id)
    {
        $request->validate([
            'status_pembayaran' => 'required|in:Menunggu,Diterima,Ditolak,Pending,Lunas,Kadaluarsa,Gagal',
        ]);

        $data = Pembayaran::where('id_pembayaran', $id)->firstOrFail();
        $data->status_pembayaran = $request->status_pembayaran;

        if (in_array($request->status_pembayaran, ['Lunas', 'Diterima'])) {
            $data->waktu_lunas = now();
        } else {
            $data->waktu_lunas = null;
        }

        $data->save();

        return redirect('/admin/pembayaran')->with('success', 'Status pembayaran berhasil diperbarui.');
    }

    /**
     * Tampilan form pembayaran untuk Orang Tua / Calon Siswa
     */
    public function pembayaran()
    {
        $user = Auth::user();
        $casis = $user->calonSiswa;

        if (!$casis) {
            if ($user->calonSiswa()->onlyTrashed()->exists()) {
                return redirect()->route('berandacasis')->with('error', 'Data pendaftaran Anda sedang diarsipkan. Silakan hubungi panitia.');
            }
            return redirect()->route('daftarcasis')->with('error', 'Silakan isi formulir pendaftaran calon siswa terlebih dahulu sebelum melakukan pembayaran.');
        }

        $tahunAjar = $casis->tahunAjaran ?? TahunAjaran::where('status', 'Berlangsung')->first();
        $biayaPendaftaran = $tahunAjar ? $tahunAjar->biaya_pendaftaran : 150000;

        $pembayaran = Pembayaran::where('id_calon_siswa', $casis->id_calon_siswa)->first();

        $status_pembayaran = $pembayaran ? $pembayaran->status_pembayaran : 'Belum Bayar';
        $bukti_pembayaran = $pembayaran ? $pembayaran->bukti_pembayaran : null;

        $clientKey = $this->midtransService->getClientKey();
        $snapJsUrl = $this->midtransService->getSnapJsUrl();
        $snapToken = $pembayaran?->snap_token;

        return view('pembayaran.bayar', compact(
            'user',
            'casis',
            'pembayaran',
            'status_pembayaran',
            'bukti_pembayaran',
            'biayaPendaftaran',
            'clientKey',
            'snapJsUrl',
            'snapToken'
        ));
    }

    /**
     * Generate Midtrans Snap Token untuk Pembayaran Formulir Pendaftaran
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

        $pembayaran = Pembayaran::where('id_calon_siswa', $casis->id_calon_siswa)->first();

        if ($pembayaran && in_array($pembayaran->status_pembayaran, ['Lunas', 'Diterima'])) {
            return response()->json(['success' => false, 'message' => 'Pembayaran pendaftaran sudah berstatus Lunas.'], 400);
        }

        $tahunAjar = $casis->tahunAjaran ?? TahunAjaran::where('status', 'Berlangsung')->first();
        $nominal = (float) ($tahunAjar ? $tahunAjar->biaya_pendaftaran : 150000);

        try {
            DB::beginTransaction();

            // Buat order_id unik baru
            $orderId = 'REG-' . date('Ymd') . '-' . str_pad($casis->id_calon_siswa, 4, '0', STR_PAD_LEFT) . '-' . time();

            // Siapkan payload Snap Midtrans
            $snapPayload = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $nominal,
                ],
                'customer_details' => [
                    'first_name' => $casis->nama_calon_siswa,
                    'email' => $user->email ?: ($user->username . '@ppdb.sch.id'),
                    'phone' => $casis->no_hp_calon_siswa ?: ($casis->no_hp_orang_tua ?: '081234567890'),
                ],
                'item_details' => [
                    [
                        'id' => 'BIAYA-REG-' . ($tahunAjar?->id_tahun_ajaran ?? '1'),
                        'price' => (int) $nominal,
                        'quantity' => 1,
                        'name' => 'Biaya Formulir Pendaftaran PPDB',
                    ],
                ],
            ];

            // Request ke Midtrans Service
            $midtransRes = $this->midtransService->createSnapTransaction($snapPayload);
            $snapToken = $midtransRes['token'];
            $paymentUrl = $midtransRes['redirect_url'];

            // PENTING: Update in-place pada baris yang sama jika sudah ada record pembayaran
            if ($pembayaran) {
                $pembayaran->update([
                    'order_id' => $orderId,
                    'tgl_pembayaran' => now(),
                    'waktu_dibuat' => $pembayaran->waktu_dibuat ?? now('UTC'),
                    'jml_pembayaran' => $nominal,
                    'snap_token' => $snapToken,
                    'payment_url' => $paymentUrl,
                    'status_pembayaran' => 'Pending',
                    'waktu_kadaluarsa' => now()->addDay(),
                    'waktu_lunas' => null,
                ]);
            } else {
                $pembayaran = Pembayaran::create([
                    'id_calon_siswa' => $casis->id_calon_siswa,
                    'order_id' => $orderId,
                    'tgl_pembayaran' => now(),
                    'waktu_dibuat' => now('UTC'),
                    'jml_pembayaran' => $nominal,
                    'snap_token' => $snapToken,
                    'payment_url' => $paymentUrl,
                    'status_pembayaran' => 'Pending',
                    'waktu_kadaluarsa' => now()->addDay(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $orderId,
                'gross_amount' => $nominal,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error createSnapToken pendaftaran: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses sesi pembayaran Midtrans: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unggah bukti transfer / konfirmasi pembayaran oleh Orang Tua
     * Sesuai ketentuan: Jika sudah ada tagihan sebelumnya (misal kedaluwarsa/ditolak/pending),
     * sistem melakukan UPDATE pada baris yang sama, BUKAN INSERT baris baru,
     * sehingga UNIQUE KEY (id_calon_siswa) tetap terjaga.
     */
    public function pelunasan(Request $request)
    {
        $request->validate([
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'bukti_pembayaran.required' => 'Bukti pembayaran harus diunggah.',
            'bukti_pembayaran.image' => 'File harus berupa gambar.',
            'bukti_pembayaran.mimes' => 'Format file harus jpeg, png, atau jpg.',
            'bukti_pembayaran.max' => 'Ukuran file tidak boleh lebih dari 2MB.',
        ]);

        $user = Auth::user();
        $casis = $user->calonSiswa;

        if (!$casis) {
            return redirect()->back()->with('error', 'Data calon siswa tidak ditemukan. Silakan melakukan pendaftaran terlebih dahulu.');
        }

        try {
            $pembayaran = Pembayaran::where('id_calon_siswa', $casis->id_calon_siswa)->first();

            // Jika status pembayaran sudah Lunas/Diterima, tidak boleh upload ulang sembarangan
            if ($pembayaran && in_array($pembayaran->status_pembayaran, ['Lunas', 'Diterima'])) {
                return redirect()->route('pembayaran')->with('error', 'Pembayaran pendaftaran sudah berstatus Lunas.');
            }

            // Simpan berkas bukti pembayaran baru
            $bukti = $request->file('bukti_pembayaran');
            $nama_bukti = 'bukti_' . $casis->id_calon_siswa . '_' . time() . '.' . $bukti->getClientOriginalExtension();
            $bukti->storeAs('public/pembayaran', $nama_bukti);

            $tahunAjar = $casis->tahunAjaran ?? TahunAjaran::where('status', 'Berlangsung')->first();
            $nominal = $tahunAjar ? $tahunAjar->biaya_pendaftaran : 150000;
            $orderId = 'PPDB-' . date('Ymd') . '-' . str_pad($casis->id_calon_siswa, 4, '0', STR_PAD_LEFT) . '-' . time();

            if ($pembayaran) {
                // Hapus bukti lama jika ada
                if ($pembayaran->bukti_pembayaran && Storage::disk('public')->exists('pembayaran/' . $pembayaran->bukti_pembayaran)) {
                    Storage::disk('public')->delete('pembayaran/' . $pembayaran->bukti_pembayaran);
                }

                // UPDATE baris yang sama
                $pembayaran->update([
                    'order_id' => $orderId,
                    'tgl_pembayaran' => now(),
                    'waktu_dibuat' => $pembayaran->waktu_dibuat ?? now('UTC'),
                    'jml_pembayaran' => $nominal,
                    'metode_pembayaran' => 'Transfer Bank',
                    'bukti_pembayaran' => $nama_bukti,
                    'status_pembayaran' => 'Menunggu',
                    'waktu_kadaluarsa' => null,
                    'waktu_lunas' => null,
                ]);
            } else {
                // INSERT baris baru jika belum pernah ada
                Pembayaran::create([
                    'id_calon_siswa' => $casis->id_calon_siswa,
                    'order_id' => $orderId,
                    'tgl_pembayaran' => now(),
                    'waktu_dibuat' => now('UTC'),
                    'jml_pembayaran' => $nominal,
                    'metode_pembayaran' => 'Transfer Bank',
                    'bukti_pembayaran' => $nama_bukti,
                    'status_pembayaran' => 'Menunggu',
                ]);
            }

            return redirect()->route('pembayaran')->with('success', 'Bukti pembayaran berhasil diunggah. Mohon tunggu verifikasi dari panitia.');
        } catch (\Exception $e) {
            Log::error('Error upload pembayaran: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Cek status pembayaran ke Midtrans Status API oleh Admin
     */
    public function checkStatus(Request $request, $id)
    {
        try {
            $pembayaran = Pembayaran::with(['calonSiswa'])->findOrFail($id);

            // Jika status pembayaran sudah Lunas / Diterima, tolak aksi cek status
            if (in_array($pembayaran->status_pembayaran, ['Lunas', 'Diterima'])) {
                return redirect()->back()->with('error', "Pembayaran untuk Order ID {$pembayaran->order_id} sudah berstatus Lunas.");
            }

            if (empty($pembayaran->order_id)) {
                return redirect()->back()->with('error', 'Data pembayaran tidak memiliki Order ID yang valid untuk diperiksa.');
            }

            $res = $this->midtransService->getTransactionStatus($pembayaran->order_id);

            $transactionStatus = $res['transaction_status'] ?? null;
            $fraudStatus = $res['fraud_status'] ?? 'accept';
            $transactionId = $res['transaction_id'] ?? null;
            $paymentType = $res['payment_type'] ?? $pembayaran->metode_pembayaran;

            // Pemetaan Status Mengikuti Logika yang Sama dengan Webhook
            $appStatus = match ($transactionStatus) {
                'capture' => ($fraudStatus === 'challenge') ? 'Pending' : 'Lunas',
                'settlement' => 'Lunas',
                'pending' => 'Pending',
                'deny', 'cancel' => 'Gagal',
                'expire' => 'Kadaluarsa',
                default => 'Pending',
            };

            // Ekstraksi Channel / Bank
            $vaNumber = null;
            $channel = null;

            if (!empty($res['va_numbers'][0]['va_number'])) {
                $vaNumber = $res['va_numbers'][0]['bank'] . ' - ' . $res['va_numbers'][0]['va_number'];
                $channel = strtolower($res['va_numbers'][0]['bank']);
            } elseif (!empty($res['permata_va_number'])) {
                $vaNumber = 'permata - ' . $res['permata_va_number'];
                $channel = 'permata';
            } elseif (!empty($res['bill_key'])) {
                $vaNumber = 'mandiri - ' . $res['bill_key'];
                $channel = 'mandiri';
            } elseif (!empty($res['store'])) {
                $channel = strtolower($res['store']);
            } elseif (!empty($res['acquirer'])) {
                $channel = strtolower($res['acquirer']);
            } elseif (!empty($res['bank'])) {
                $channel = strtolower($res['bank']);
            } else {
                $channel = strtolower($paymentType ?: '');
            }

            $oldStatus = $pembayaran->status_pembayaran;
            $rawMidtransTime = $res['settlement_time'] ?? $res['transaction_time'] ?? null;
            $utcSettlementTime = parse_midtrans_time($rawMidtransTime) ?? now('UTC');

            $updateData = [
                'transaction_id' => $transactionId ?: $pembayaran->transaction_id,
                'status_pembayaran' => $appStatus,
                'metode_pembayaran' => $paymentType ?: $pembayaran->metode_pembayaran,
                'channel' => $channel ?: $pembayaran->channel,
            ];

            if ($vaNumber) {
                $updateData['va_number'] = $vaNumber;
            }

            if (!$pembayaran->waktu_dibuat) {
                $rawCreatedTime = $res['transaction_time'] ?? null;
                $updateData['waktu_dibuat'] = parse_midtrans_time($rawCreatedTime) ?? $pembayaran->tgl_pembayaran ?? now('UTC');
            }

            if ($appStatus === 'Lunas') {
                $updateData['waktu_lunas'] = $pembayaran->waktu_lunas ?? $utcSettlementTime;
                $updateData['tgl_pembayaran'] = $updateData['waktu_lunas'];
            } elseif ($appStatus === 'Kadaluarsa') {
                $updateData['waktu_kadaluarsa'] = now('UTC');
            }

            $pembayaran->update($updateData);

            // Audit log aman tanpa data sensitif (server key, nomor kartu, dsb)
            $adminId = Auth::id();
            Log::info("[Admin Check Status] Admin ID: {$adminId}, Order ID: {$pembayaran->order_id}, Old Status: {$oldStatus}, New Status: {$appStatus}");

            return redirect()->back()->with('success', "Status pembayaran Order ID {$pembayaran->order_id} berhasil disinkronkan ke Midtrans: {$appStatus}.");
        } catch (\Exception $e) {
            Log::warning("[Admin Check Status Error] ID {$id}: " . $e->getMessage());
            return redirect()->back()->with('error', "Gagal memeriksa status ke Midtrans: " . $e->getMessage());
        }
    }

    /**
     * Hapus data pembayaran oleh Admin
     * Dinonaktifkan di sisi server demi menjaga integritas data transaksi dan kelulusan seleksi.
     */
    public function delete($id)
    {
        return redirect()->route('index')->with('error', 'Penghapusan data pembayaran dinonaktifkan demi menjaga integritas data transaksi dan kelulusan seleksi.');
    }
}
