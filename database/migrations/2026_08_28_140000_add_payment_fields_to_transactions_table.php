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
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'invoice_number')) {
                $table->string('invoice_number', 50)->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('transactions', 'admin_fee')) {
                $table->decimal('admin_fee', 12, 2)->default(0)->after('price');
            }
            if (! Schema::hasColumn('transactions', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->default(0)->after('admin_fee');
            }
            if (! Schema::hasColumn('transactions', 'payment_status')) {
                $table->enum('payment_status', ['unpaid', 'pending', 'settlement', 'expired', 'failed', 'refunded'])
                      ->default('unpaid')
                      ->after('status');
            }
            if (! Schema::hasColumn('transactions', 'payment_channel')) {
                $table->string('payment_channel', 50)->nullable()->after('payment_status');
            }
            if (! Schema::hasColumn('transactions', 'gateway_reference_id')) {
                $table->string('gateway_reference_id', 100)->nullable()->after('payment_channel');
            }
            if (! Schema::hasColumn('transactions', 'payment_token')) {
                $table->string('payment_token', 255)->nullable()->after('gateway_reference_id');
            }
            if (! Schema::hasColumn('transactions', 'payment_url')) {
                $table->text('payment_url')->nullable()->after('payment_token');
            }
            if (! Schema::hasColumn('transactions', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('completed_at');
            }
            if (! Schema::hasColumn('transactions', 'expired_at')) {
                $table->timestamp('expired_at')->nullable()->after('paid_at');
            }
            if (! Schema::hasColumn('transactions', 'raw_response')) {
                $table->json('raw_response')->nullable()->after('expired_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $columns = [
                'invoice_number',
                'admin_fee',
                'total_amount',
                'payment_status',
                'payment_channel',
                'gateway_reference_id',
                'payment_token',
                'payment_url',
                'paid_at',
                'expired_at',
                'raw_response',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
