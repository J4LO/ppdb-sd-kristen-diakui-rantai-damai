<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected string $serverKey;
    protected string $clientKey;
    protected bool $isProduction;
    protected string $snapApiUrl;
    protected string $snapJsUrl;
    protected string $coreApiUrl;

    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key', '');
        $this->clientKey = config('midtrans.client_key', '');
        $this->isProduction = (bool) config('midtrans.is_production', false);
        $this->snapApiUrl = config('midtrans.snap_api_url');
        $this->snapJsUrl = config('midtrans.snap_js_url');
        $this->coreApiUrl = config('midtrans.core_api_url', $this->isProduction ? 'https://api.midtrans.com/v2' : 'https://api.sandbox.midtrans.com/v2');
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function getSnapJsUrl(): string
    {
        return $this->snapJsUrl;
    }

    /**
     * Request Snap Token ke Midtrans
     *
     * @param array $payload
     * @return array ['token' => string, 'redirect_url' => string]
     * @throws \Exception
     */
    public function createSnapTransaction(array $payload): array
    {
        try {
            $authHeader = base64_encode($this->serverKey . ':');

            $httpClient = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . $authHeader,
            ])->timeout(30);

            // Pengaturan SSL Verification untuk lingkungan Windows / XAMPP
            $verifySsl = config('midtrans.verify_ssl', true);
            $caBundle = config('midtrans.ca_bundle');
            $defaultXamppCa = 'C:/xampp/php/extras/ssl/cacert.pem';

            if (!$verifySsl) {
                $httpClient = $httpClient->withoutVerifying();
            } elseif ($caBundle && file_exists($caBundle)) {
                $httpClient = $httpClient->withOptions(['verify' => $caBundle]);
            } elseif (file_exists($defaultXamppCa)) {
                $httpClient = $httpClient->withOptions(['verify' => $defaultXamppCa]);
            }

            $response = $httpClient->post($this->snapApiUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'token' => $data['token'] ?? null,
                    'redirect_url' => $data['redirect_url'] ?? null,
                ];
            }

            Log::error('Midtrans Snap Error: ' . $response->body(), [
                'payload' => $payload,
                'status' => $response->status(),
            ]);

            throw new \Exception('Midtrans Error: ' . ($response->json('error_messages.0') ?? $response->body()));
        } catch (\Exception $e) {
            Log::error('Midtrans Exception: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verifikasi Signature Key Notifikasi Webhook Midtrans (SHA-512)
     *
     * @param string $orderId
     * @param string $statusCode
     * @param string|float $grossAmount
     * @param string $signatureKey
     * @return bool
     */
    public function verifySignature(string $orderId, string $statusCode, $grossAmount, string $signatureKey): bool
    {
        // Pastikan format gross_amount konsisten dengan string dari payload Midtrans
        $formattedAmount = is_numeric($grossAmount) ? number_format((float)$grossAmount, 2, '.', '') : (string)$grossAmount;
        
        $expectedSignature1 = hash('sha512', $orderId . $statusCode . $formattedAmount . $this->serverKey);
        $expectedSignature2 = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        return hash_equals($expectedSignature1, $signatureKey) || hash_equals($expectedSignature2, $signatureKey);
    }

    /**
     * Cek status transaksi pembayaran ke Midtrans Status API (GET /v2/{order_id}/status)
     *
     * @param string $orderId
     * @return array
     * @throws \Exception
     */
    public function getTransactionStatus(string $orderId): array
    {
        try {
            $authHeader = base64_encode($this->serverKey . ':');
            $url = rtrim($this->coreApiUrl, '/') . '/' . urlencode($orderId) . '/status';

            $httpClient = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . $authHeader,
            ])->timeout(10);

            // Pengaturan SSL Verification untuk lingkungan Windows / XAMPP
            $verifySsl = config('midtrans.verify_ssl', true);
            $caBundle = config('midtrans.ca_bundle');
            $defaultXamppCa = 'C:/xampp/php/extras/ssl/cacert.pem';

            if (!$verifySsl) {
                $httpClient = $httpClient->withoutVerifying();
            } elseif ($caBundle && file_exists($caBundle)) {
                $httpClient = $httpClient->withOptions(['verify' => $caBundle]);
            } elseif (file_exists($defaultXamppCa)) {
                $httpClient = $httpClient->withOptions(['verify' => $defaultXamppCa]);
            }

            $response = $httpClient->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            $statusCode = $response->status();
            $body = $response->json();
            $errorMessage = $body['status_message'] ?? ('HTTP Error ' . $statusCode);

            Log::warning("Midtrans Status API returned HTTP {$statusCode} for Order ID {$orderId}: " . json_encode($body));

            throw new \Exception("Midtrans Status API ({$statusCode}): {$errorMessage}");
        } catch (\Exception $e) {
            Log::error("Midtrans getTransactionStatus Exception for Order ID {$orderId}: " . $e->getMessage());
            throw $e;
        }
    }
}

