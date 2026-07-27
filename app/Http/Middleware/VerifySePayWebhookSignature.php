<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySePayWebhookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('commerce.sepay.webhook_secret', '');
        if ($secret === '') {
            return $this->error('Webhook secret is not configured.', 503);
        }

        $signature = (string) $request->header('X-SePay-Signature', '');
        $timestampValue = (string) $request->header('X-SePay-Timestamp', '');
        if (! ctype_digit($timestampValue)) {
            return $this->error('Invalid timestamp.', 401);
        }

        $timestamp = (int) $timestampValue;
        if ($timestamp <= 0 || abs(now()->timestamp - $timestamp) > 300) {
            return $this->error('Request expired.', 401);
        }

        $expected = 'sha256='.hash_hmac(
            'sha256',
            $timestampValue.'.'.$request->getContent(),
            $secret,
        );

        if ($signature === '' || ! hash_equals($expected, $signature)) {
            return $this->error('Invalid signature.', 401);
        }

        $request->attributes->set('sepay_signature_verified', true);

        return $next($request);
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }
}
