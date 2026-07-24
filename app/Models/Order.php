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
        'status', 'payment_status', 'payment_method', 'subtotal_amount', 'discount_amount',
        'shipping_amount', 'total_amount', 'currency', 'note', 'admin_note', 'requires_invoice',
        'invoice_company', 'invoice_tax_code', 'assigned_to',
    ];

    protected $casts = [
        'subtotal_amount' => 'decimal:2', 'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2', 'total_amount' => 'decimal:2',
        'requires_invoice' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function coupon(): BelongsTo { return $this->belongsTo(Coupon::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function statusHistories(): HasMany { return $this->hasMany(OrderStatusHistory::class); }
}
