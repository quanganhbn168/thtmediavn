<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 120)->nullable()->unique();
            $table->string('coupon_code', 60)->nullable();
            $table->timestamps();
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
