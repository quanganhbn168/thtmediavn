<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class SePayService
{
    public function isEnabled(): bool
    {
        $config = config('commerce.sepay', []);

        return (bool) ($config['enabled'] ?? false)
            && collect(['bank_code', 'account_name', 'account_number', 'webhook_secret'])
                ->every(fn (string $key): bool => filled($config[$key] ?? null));
    }

    public function paymentCode(): string
    {
        $prefix = preg_replace('/[^A-Z0-9]/', '', strtoupper((string) config('commerce.sepay.payment_prefix', 'RHEA'))) ?: 'RHEA';

        do {
            $code = $prefix.Str::upper(Str::random(12));
        } while (Order::query()->where('payment_code', $code)->exists());

        return $code;
    }

    public function publicToken(): string
    {
        do {
            $token = Str::random(64);
        } while (Order::query()->where('payment_public_token', $token)->exists());

        return $token;
    }

    public function qrUrl(Order $order): string
    {
        return 'https://vietqr.app/img?'.http_build_query([
            'acc' => config('commerce.sepay.account_number'),
            'bank' => config('commerce.sepay.bank_code'),
            'amount' => (int) round((float) $order->total_amount),
            'des' => $order->payment_code,
        ], '', '&', PHP_QUERY_RFC3986);
    }
}
