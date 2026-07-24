<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'phone', 'email', 'address', 'birthday', 'gender', 'notes', 'is_active'];

    protected $casts = ['birthday' => 'date', 'is_active' => 'boolean'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
