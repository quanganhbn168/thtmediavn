<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SiteAsset extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const COLLECTIONS = [
        'logo', 'logo_footer', 'favicon', 'seo_image', 'about_image',
        'default_product_banner', 'default_promotion_banner', 'default_post_banner',
    ];

    protected $fillable = ['key'];

    public static function current(): self
    {
        return self::query()->firstOrCreate(['key' => 'site']);
    }

    public function registerMediaCollections(): void
    {
        foreach (self::COLLECTIONS as $collection) {
            $this->addMediaCollection($collection)->singleFile()->useDisk('public_media');
        }
    }
}
