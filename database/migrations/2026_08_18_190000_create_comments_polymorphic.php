<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('comments') || ! Schema::hasColumn('comments', 'post_id') || Schema::hasColumn('comments', 'commentable_id')) {
            return;
        }

        Schema::table('comments', function (Blueprint $table): void {
            $table->nullableMorphs('commentable');
        });

        DB::table('comments')->update(['commentable_type' => Post::class]);
        DB::statement('UPDATE comments SET commentable_id = post_id WHERE post_id IS NOT NULL');

        Schema::table('comments', function (Blueprint $table): void {
            $table->dropForeign(['post_id']);
            $table->dropColumn('post_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('comments') || ! Schema::hasColumn('comments', 'commentable_id') || Schema::hasColumn('comments', 'post_id')) {
            return;
        }

        Schema::table('comments', function (Blueprint $table): void {
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
        });

        DB::table('comments')
            ->where('commentable_type', Post::class)
            ->update(['post_id' => DB::raw('commentable_id')]);

        Schema::table('comments', function (Blueprint $table): void {
            $table->dropIndex('comments_commentable_type_commentable_id_index');
            $table->dropColumn(['commentable_type', 'commentable_id']);
        });
    }
};
