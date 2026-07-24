<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_code', 50)->unique();
                $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
                $table->string('customer_name', 150);
                $table->string('customer_phone', 50);
                $table->string('customer_email', 100)->nullable();
                $table->string('customer_address', 255)->nullable();
                $table->string('order_type', 50)->default('website');
                $table->string('shipping_province', 100)->nullable();
                $table->string('shipping_district', 100)->nullable();
                $table->string('shipping_ward', 100)->nullable();
                $table->string('shipping_address', 255)->nullable();
                $table->string('status', 30)->default('pending');
                $table->string('payment_status', 30)->default('unpaid');
                $table->string('payment_method', 50)->nullable();
                $table->decimal('subtotal_amount', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('shipping_amount', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->decimal('deposit_amount', 15, 2)->default(0);
                $table->decimal('paid_amount', 15, 2)->default(0);
                $table->decimal('remaining_amount', 15, 2)->default(0);
                $table->string('currency', 10)->default('VND');
                $table->boolean('requires_invoice')->default(false);
                $table->string('invoice_company', 255)->nullable();
                $table->string('invoice_tax_code', 50)->nullable();
                $table->text('note')->nullable();
                $table->text('admin_note')->nullable();
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'coupon_id')) {
                $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'shipping_province')) {
                $table->string('shipping_province', 100)->nullable();
            }
            if (! Schema::hasColumn('orders', 'shipping_district')) {
                $table->string('shipping_district', 100)->nullable();
            }
            if (! Schema::hasColumn('orders', 'shipping_ward')) {
                $table->string('shipping_ward', 100)->nullable();
            }
            if (! Schema::hasColumn('orders', 'shipping_address')) {
                $table->string('shipping_address', 255)->nullable();
            }
            if (! Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method', 50)->nullable();
            }
            if (! Schema::hasColumn('orders', 'shipping_amount')) {
                $table->decimal('shipping_amount', 15, 2)->default(0);
            }
            if (! Schema::hasColumn('orders', 'requires_invoice')) {
                $table->boolean('requires_invoice')->default(false);
            }
            if (! Schema::hasColumn('orders', 'invoice_company')) {
                $table->string('invoice_company', 255)->nullable();
            }
            if (! Schema::hasColumn('orders', 'invoice_tax_code')) {
                $table->string('invoice_tax_code', 50)->nullable();
            }
            if (! Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 10)->default('VND');
            }
            if (! Schema::hasColumn('orders', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
