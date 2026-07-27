<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SePayApiClient
{
    public function transactions(array $query): array
    {
        $response = $this->request()->get('/transactions', $query)->throw();
        $payload = $response->json();

        if (! is_array($payload) || ($payload['status'] ?? null) !== 'success' || ! is_array($payload['data'] ?? null)) {
            throw new RuntimeException('SePay API trả về dữ liệu giao dịch không hợp lệ.');
        }

        return $payload;
    }

    public function testConnection(): array
    {
        $response = $this->request()->get('/bank-accounts', ['per_page' => 1])->throw();
        $payload = $response->json();

        if (! is_array($payload) || ($payload['status'] ?? null) !== 'success') {
            throw new RuntimeException('Không thể xác minh kết nối SePay API.');
        }

        return $payload;
    }

    private function request(): PendingRequest
    {
        $token = (string) config('commerce.sepay.api_token', '');
        $baseUrl = (string) config('commerce.sepay.api_base_url', '');
        if ($token === '' || $baseUrl === '') {
            throw new RuntimeException('SePay API token hoặc endpoint chưa được cấu hình.');
        }

        return Http::baseUrl($baseUrl)
            ->withToken($token)
            ->acceptJson()
            ->timeout(15)
            ->connectTimeout(5)
            ->retry(3, 500);
    }
}
