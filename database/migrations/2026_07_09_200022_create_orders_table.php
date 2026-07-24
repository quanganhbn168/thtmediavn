<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 50)->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('customer_name', 150);
            $table->string('customer_phone', 50);
            $table->string('customer_email', 100)->nullable();
            $table->string('customer_address', 255)->nullable();
            $table->string('order_type', 50); // Trường tương thích schema cũ; migration e-commerce phía sau sẽ thay thế bảng.
            $table->string('status', 30)->default('pending'); // pending, processing, confirmed, completed, cancelled
            $table->string('payment_status', 30)->default('unpaid'); // unpaid, partial, paid, refunded
            $table->decimal('subtotal_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('deposit_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->string('currency', 10)->default('VND');
            $table->text('note')->nullable();
            $table->text('admin_note')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable(); // Giao việc cho nhân viên chăm sóc
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
