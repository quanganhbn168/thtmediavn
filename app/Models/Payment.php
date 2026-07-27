<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['order_id', 'payment_transaction_id', 'payment_code', 'amount', 'method', 'status', 'transaction_id', 'payment_date', 'note', 'created_by', 'is_automatic'];

    protected $casts = ['amount' => 'decimal:2', 'payment_date' => 'datetime', 'is_automatic' => 'boolean'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }
}
