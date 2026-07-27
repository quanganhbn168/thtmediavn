<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSyncState;
use App\Models\PaymentTransaction;
use App\Services\SePayApiClient;
use App\Services\SePayReconciliationService;
use App\Services\SePayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class SePaySettingController extends Controller
{
    public function index(SePayService $sePay): View
    {
        $config = config('commerce.sepay', []);

        return view('admin.settings.payment', [
            'sePayEnabled' => $sePay->isEnabled(),
            'sePay' => collect($config)->except(['webhook_secret', 'api_token'])->all(),
            'hasWebhookSecret' => filled($config['webhook_secret'] ?? null),
            'hasApiToken' => filled($config['api_token'] ?? null),
            'webhookUrl' => route('api.webhooks.sepay'),
            'lastWebhookAt' => PaymentTransaction::query()->where('source', 'webhook')->max('received_at'),
            'syncState' => PaymentSyncState::find('sepay'),
            'unmatchedCount' => PaymentTransaction::query()->whereIn('match_status', ['unmatched', 'amount_mismatch', 'late'])->count(),
        ]);
    }

    public function testConnection(SePayApiClient $api): RedirectResponse
    {
        try {
            $api->testConnection();

            return back()->with('success', 'Kết nối SePay API v2 thành công.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Không thể kết nối SePay API: '.$exception->getMessage());
        }
    }

    public function reconcile(SePayReconciliationService $reconciliation): RedirectResponse
    {
        try {
            $result = $reconciliation->reconcile();

            return back()->with('success', "Đối soát xong {$result['processed']} giao dịch; khớp {$result['matched']}; cần kiểm tra {$result['unmatched']}.");
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Đối soát SePay thất bại: '.$exception->getMessage());
        }
    }
}
