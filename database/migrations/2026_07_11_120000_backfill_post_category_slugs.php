<?php

use App\Models\PostCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('post_categories')->orderBy('id')->each(function (object $category): void {
            $names = json_decode($category->name, true) ?: [];

            foreach ($names as $locale => $name) {
                if (! is_string($name) || trim($name) === '') {
                    continue;
                }

                $exists = DB::table('slugs')
                    ->where('sluggable_type', PostCategory::class)
                    ->where('sluggable_id', $category->id)
                    ->where('locale', $locale)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $base = Str::slug($name);
                $slug = $base;
                $suffix = 1;

                while (DB::table('slugs')->where('slug', $slug)->where('locale', $locale)->exists()) {
                    $slug = $base.'-'.$suffix++;
                }

                DB::table('slugs')->insert([
                    'slug' => $slug,
                    'sluggable_type' => PostCategory::class,
                    'sluggable_id' => $category->id,
                    'locale' => $locale,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Data repair: slugs are intentionally preserved on rollback.
    }
};
