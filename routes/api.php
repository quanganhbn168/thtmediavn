<?php

use App\Http\Controllers\Api\SePayWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/sepay', SePayWebhookController::class)
    ->middleware(['throttle:sepay-webhook', 'sepay.webhook'])
    ->name('api.webhooks.sepay');
