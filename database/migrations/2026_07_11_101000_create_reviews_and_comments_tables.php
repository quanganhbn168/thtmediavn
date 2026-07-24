<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up():void { Schema::create('reviews',function(Blueprint $table){$table->id();$table->morphs('reviewable');$table->string('name',120);$table->string('email',150)->nullable();$table->unsignedTinyInteger('rating');$table->text('content');$table->string('status',20)->default('pending');$table->boolean('is_verified')->default(false);$table->timestamps();$table->index(['status','created_at']);});Schema::create('comments',function(Blueprint $table){$table->id();$table->foreignId('post_id')->constrained()->cascadeOnDelete();$table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();$table->string('name',120);$table->string('email',150)->nullable();$table->text('content');$table->string('status',20)->default('pending');$table->timestamps();$table->index(['post_id','status','created_at']);}); }
    public function down():void { Schema::dropIfExists('comments');Schema::dropIfExists('reviews'); }
};
