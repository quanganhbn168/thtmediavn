<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSyncState extends Model
{
    protected $primaryKey = 'provider';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'provider', 'last_transaction_id', 'last_reconciled_at', 'last_status',
        'last_error', 'last_processed_count',
    ];

    protected $casts = [
        'last_reconciled_at' => 'datetime',
        'last_processed_count' => 'integer',
    ];
}
