<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('payment_provider', 30)->nullable()->after('payment_method');
            $table->string('payment_code', 50)->nullable()->unique()->after('payment_provider');
            $table->string('payment_public_token', 64)->nullable()->unique()->after('payment_code');
            $table->timestamp('payment_expires_at')->nullable()->after('payment_public_token');
            $table->timestamp('paid_at')->nullable()->after('payment_expires_at');
            $table->timestamp('stock_reserved_at')->nullable()->after('paid_at');
            $table->timestamp('stock_released_at')->nullable()->after('stock_reserved_at');
            $table->timestamp('sold_count_recorded_at')->nullable()->after('stock_released_at');
            $table->index(['status', 'payment_expires_at'], 'orders_payment_expiry_index');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->boolean('stock_reserved')->default(false)->after('quantity');
        });

        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 30)->default('sepay');
            $table->string('source', 20)->default('webhook');
            $table->string('provider_transaction_id', 100);
            $table->string('deduplication_key', 64);
            $table->string('reference_code', 150)->nullable();
            $table->string('bank_gateway', 100)->nullable();
            $table->string('account_number', 100)->nullable();
            $table->string('payment_code', 50)->nullable();
            $table->text('transaction_content')->nullable();
            $table->string('transfer_type', 10)->nullable();
            $table->unsignedBigInteger('amount')->default(0);
            $table->timestamp('transaction_at')->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('match_status', 30)->default('unmatched');
            $table->boolean('signature_verified')->default(false);
            $table->json('raw_payload');
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->text('processing_error')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_transaction_id'], 'payment_transactions_provider_id_unique');
            $table->unique(['provider', 'deduplication_key'], 'payment_transactions_dedup_unique');
            $table->index(['match_status', 'transaction_at']);
            $table->index('payment_code');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->foreignId('payment_transaction_id')->nullable()->after('order_id')->unique()
                ->constrained('payment_transactions')->nullOnDelete();
            $table->boolean('is_automatic')->default(false)->after('created_by');
        });

        Schema::table('payment_transactions', function (Blueprint $table): void {
            $table->foreignId('payment_id')->nullable()->after('order_id')->unique()
                ->constrained('payments')->nullOnDelete();
        });

        Schema::create('payment_sync_states', function (Blueprint $table): void {
            $table->string('provider', 30)->primary();
            $table->string('last_transaction_id', 100)->nullable();
            $table->timestamp('last_reconciled_at')->nullable();
            $table->string('last_status', 20)->nullable();
            $table->text('last_error')->nullable();
            $table->unsignedInteger('last_processed_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_sync_states');

        Schema::table('payment_transactions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('payment_id');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('payment_transaction_id');
            $table->dropColumn('is_automatic');
        });

        Schema::dropIfExists('payment_transactions');

        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn('stock_reserved');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('orders_payment_expiry_index');
            $table->dropUnique(['payment_code']);
            $table->dropUnique(['payment_public_token']);
            $table->dropColumn([
                'payment_provider',
                'payment_code',
                'payment_public_token',
                'payment_expires_at',
                'paid_at',
                'stock_reserved_at',
                'stock_released_at',
                'sold_count_recorded_at',
            ]);
        });
    }
};
