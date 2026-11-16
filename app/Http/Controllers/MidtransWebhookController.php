<?php

namespace App\Http\Controllers;

use App\Models\CalonSiswa;
use App\Models\DaftarUlang;
use App\Models\Pembayaran;
use App\Models\PembayaranDaftarUlang;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    protected MidtransService $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Menangani callback webhook notifikasi pembayaran dari Midtrans
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Midtrans Webhook Received: ', $payload);

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? 'accept';
        $transactionId = $payload['transaction_id'] ?? null;
        $paymentType = $payload['payment_type'] ?? 'midtrans';

        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
            Log::warning('Midtrans Webhook: Payload parameters incomplete.');
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // 1. Verifikasi Signature SHA-512
        if (!$this->midtransService->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning('Midtrans Webhook: Invalid signature key for Order ID ' . $orderId);
            return response()->json(['message' => 'Invalid signature key'], 403);
        }

        // 2. Ekstrak VA Number dan Channel/Bank jika ada
        $vaNumber = null;
        $channel = null;

        if (!empty($payload['va_numbers'][0]['va_number'])) {
            $vaNumber = $payload['va_numbers'][0]['bank'] . ' - ' . $payload['va_numbers'][0]['va_number'];
            $channel = strtolower($payload['va_numbers'][0]['bank']);
        } elseif (!empty($payload['permata_va_number'])) {
            $vaNumber = 'permata - ' . $payload['permata_va_number'];
            $channel = 'permata';
        } elseif (!empty($payload['bill_key'])) {
            $vaNumber = 'mandiri - ' . $payload['bill_key'];
            $channel = 'mandiri';
        } elseif (!empty($payload['store'])) {
            $channel = strtolower($payload['store']);
        } elseif (!empty($payload['acquirer'])) {
            $channel = strtolower($payload['acquirer']);
        } elseif (!empty($payload['bank'])) {
            $channel = strtolower($payload['bank']);
        } else {
            $channel = strtolower($paymentType);
        }

        // 3. Pemetaan Status Transaksi Midtrans
        $appStatus = match ($transactionStatus) {
            'capture' => ($fraudStatus === 'challenge') ? 'Pending' : 'Lunas',
            'settlement' => 'Lunas',
            'pending' => 'Pending',
            'deny', 'cancel' => 'Gagal',
            'expire' => 'Kadaluarsa',
            default => 'Pending',
        };

        DB::beginTransaction();
        try {
            // A. Cek apakah pembayaran formulir pendaftaran
            $pembayaran = Pembayaran::where('order_id', $orderId)->first();

            if ($pembayaran) {
                // Jangan turunkan status jika pembayaran sudah berstatus Lunas sebelumnya
                if ($pembayaran->status_pembayaran === 'Lunas' && $appStatus !== 'Lunas') {
                    DB::commit();
                    Log::info("Midtrans Webhook: Pembayaran Pendaftaran Order ID {$orderId} sudah Lunas, mengabaikan perubahan status menjadi {$appStatus}");
                    return response()->json(['status' => 'ignored', 'message' => 'Payment already settled', 'type' => 'pendaftaran']);
                }

                $updateData = [
                    'transaction_id' => $transactionId,
                    'status_pembayaran' => $appStatus,
                    'metode_pembayaran' => $paymentType,
                    'channel' => $channel,
                ];

                if ($vaNumber) {
                    $updateData['va_number'] = $vaNumber;
                }

                if (!$pembayaran->waktu_dibuat) {
                    $rawCreatedTime = $payload['transaction_time'] ?? null;
                    $updateData['waktu_dibuat'] = parse_midtrans_time($rawCreatedTime) ?? $pembayaran->tgl_pembayaran ?? now('UTC');
                }

                $rawMidtransTime = $payload['settlement_time'] ?? $payload['transaction_time'] ?? null;
                $utcSettlementTime = parse_midtrans_time($rawMidtransTime) ?? now('UTC');

                if ($appStatus === 'Lunas') {
                    $updateData['waktu_lunas'] = $pembayaran->waktu_lunas ?? $utcSettlementTime;
                    $updateData['tgl_pembayaran'] = $updateData['waktu_lunas'];
                } elseif ($appStatus === 'Kadaluarsa') {
                    $updateData['waktu_kadaluarsa'] = now('UTC');
                }

                $pembayaran->update($updateData);

                DB::commit();
                Log::info("Midtrans Webhook: Pembayaran Pendaftaran Order ID {$orderId} status diupdate menjadi {$appStatus}");
                return response()->json(['status' => 'success', 'type' => 'pendaftaran']);
            }

            // B. Cek apakah pembayaran daftar ulang
            $pembayaranDu = PembayaranDaftarUlang::where('order_id_du', $orderId)->first();

            if ($pembayaranDu) {
                $statusDu = match ($appStatus) {
                    'Lunas' => 'Lunas',
                    'Pending' => 'Pending',
                    'Kadaluarsa' => 'Kadaluarsa',
                    'Gagal' => 'Ditolak',
                    default => 'Pending',
                };

                $updateDataDu = [
                    'transaction_id_du' => $transactionId,
                    'status_du' => $statusDu,
                    'metode_pembayaran' => $paymentType,
                ];

                if ($vaNumber) {
                    $updateDataDu['va_number'] = $vaNumber;
                }

                $rawMidtransTime = $payload['settlement_time'] ?? $payload['transaction_time'] ?? null;
                $utcSettlementTime = parse_midtrans_time($rawMidtransTime) ?? now('UTC');

                if ($statusDu === 'Lunas') {
                    $updateDataDu['waktu_lunas'] = $utcSettlementTime;
                    $updateDataDu['tgl_bayar_du'] = $utcSettlementTime;
                } elseif ($statusDu === 'Kadaluarsa') {
                    $updateDataDu['waktu_kadaluarsa'] = now('UTC');
                }

                $pembayaranDu->update($updateDataDu);

                // Sinkronisasi status akhir pada tb_daftar_ulang
                $daftarUlang = $pembayaranDu->daftarUlang;
                if ($daftarUlang) {
                    $totalBiaya = (float) ($daftarUlang->calonSiswa?->tahunAjaran?->biaya_daftar_ulang ?? 5000000);
                    $totalLunas = $daftarUlang->pembayaranDaftarUlang()
                        ->whereIn('status_du', ['Lunas', 'Diterima'])
                        ->sum('jml_bayar_du');

                    if ($totalLunas >= $totalBiaya) {
                        $daftarUlang->update(['status_daftar_ulang' => 'Selesai']);
                    } else {
                        $daftarUlang->update(['status_daftar_ulang' => 'Menunggu']);
                    }
                }

                DB::commit();
                Log::info("Midtrans Webhook: Pembayaran Daftar Ulang Order ID {$orderId} status diupdate menjadi {$statusDu}");
                return response()->json(['status' => 'success', 'type' => 'daftar_ulang']);
            }

            DB::rollBack();
            Log::warning("Midtrans Webhook: Order ID {$orderId} tidak ditemukan di sistem.");
            return response()->json(['message' => 'Order not found'], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error processing webhook: ' . $e->getMessage()], 500);
        }
    }
}

