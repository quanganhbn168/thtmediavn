<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'code',
        'name',
        'native_name',
        'flag',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted()
    {
        static::saved(function () {
            cache()->forget('active_languages');
        });

        static::deleted(function () {
            cache()->forget('active_languages');
        });
    }

    /**
     * Lấy danh sách các ngôn ngữ đang hoạt động (có cache bảo vệ)
     */
    public static function getActiveLanguages()
    {
        return cache()->remember('active_languages', 86400, function () {
            try {
                return self::where('is_active', true)->orderBy('sort_order')->get();
            } catch (\Exception $e) {
                return collect();
            }
        });
    }
}
