<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('payment_code', 50)->unique();
                $table->decimal('amount', 15, 2)->default(0);
                $table->string('method', 50);
                $table->string('status', 30)->default('pending');
                $table->string('transaction_id', 100)->nullable();
                $table->timestamp('payment_date')->nullable();
                $table->text('note')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
