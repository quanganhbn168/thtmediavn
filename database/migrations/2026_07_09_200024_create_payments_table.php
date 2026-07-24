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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('payment_code', 50)->unique();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('method', 50); // cash, bank_transfer, vnpay, momo, zalopay
            $table->string('status', 30)->default('pending'); // pending, completed, failed, refunded
            $table->string('transaction_id', 100)->nullable(); // Mã giao dịch ngân hàng hoặc cổng thanh toán
            $table->timestamp('payment_date')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
