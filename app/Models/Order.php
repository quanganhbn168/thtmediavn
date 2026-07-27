<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_code', 'order_type', 'user_id', 'coupon_id', 'customer_name', 'customer_phone', 'customer_email',
        'shipping_province', 'shipping_district', 'shipping_ward', 'shipping_address',
        'status', 'payment_status', 'payment_method', 'payment_provider', 'payment_code',
        'payment_public_token', 'payment_expires_at', 'paid_at', 'stock_reserved_at',
        'stock_released_at', 'sold_count_recorded_at', 'subtotal_amount', 'discount_amount',
        'shipping_amount', 'total_amount', 'currency', 'note', 'admin_note', 'requires_invoice',
        'invoice_company', 'invoice_tax_code', 'assigned_to', 'paid_amount', 'remaining_amount',
    ];

    protected $casts = [
        'subtotal_amount' => 'decimal:2', 'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2', 'total_amount' => 'decimal:2',
        'requires_invoice' => 'boolean',
        'payment_expires_at' => 'datetime', 'paid_at' => 'datetime',
        'stock_reserved_at' => 'datetime', 'stock_released_at' => 'datetime',
        'sold_count_recorded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
}
