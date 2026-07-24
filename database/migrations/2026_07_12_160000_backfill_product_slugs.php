<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $locale = (string) config('app.locale', 'vi');

        DB::table('products')
            ->select(['id', 'name', 'slug'])
            ->orderBy('id')
            ->each(function (object $product) use ($locale): void {
                $base = Str::slug((string) ($product->slug ?: $product->name));
                if ($base === '') {
                    $base = 'san-pham-' . $product->id;
                }

                $existingSlugId = DB::table('slugs')
                    ->where('sluggable_type', Product::class)
                    ->where('sluggable_id', $product->id)
                    ->where('locale', $locale)
                    ->value('id');
                $slug = $base;
                $suffix = 1;

                while (
                    DB::table('slugs')
                        ->where('slug', $slug)
                        ->where('locale', $locale)
                        ->when($existingSlugId, fn ($query) => $query->where('id', '!=', $existingSlugId))
                        ->exists()
                    || DB::table('products')
                        ->where('slug', $slug)
                        ->where('id', '!=', $product->id)
                        ->exists()
                ) {
                    $slug = $base . '-' . $suffix++;
                }

                if ($product->slug !== $slug) {
                    DB::table('products')->where('id', $product->id)->update(['slug' => $slug]);
                }

                DB::table('slugs')->updateOrInsert(
                    [
                        'sluggable_type' => Product::class,
                        'sluggable_id' => $product->id,
                        'locale' => $locale,
                    ],
                    [
                        'slug' => $slug,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            });
    }

    public function down(): void
    {
        DB::table('slugs')->where('sluggable_type', Product::class)->delete();
    }
};
