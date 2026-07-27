<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    public const MATCH_STATUSES = [
        'matched' => 'Đã khớp',
        'unmatched' => 'Chưa khớp',
        'amount_mismatch' => 'Sai số tiền',
        'late' => 'Thanh toán muộn',
        'duplicate' => 'Bị trùng',
        'ignored' => 'Bỏ qua',
    ];

    protected $fillable = [
        'provider', 'source', 'provider_transaction_id', 'deduplication_key',
        'reference_code', 'bank_gateway', 'account_number', 'payment_code',
        'transaction_content', 'transfer_type', 'amount', 'transaction_at',
        'order_id', 'payment_id', 'match_status', 'signature_verified', 'raw_payload',
        'received_at', 'processed_at', 'processing_error',
    ];

    protected $casts = [
        'amount' => 'integer',
        'transaction_at' => 'datetime',
        'signature_verified' => 'boolean',
        'raw_payload' => 'array',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
