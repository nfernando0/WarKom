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
            $table->string('invoice_number', 50)->unique()->after('id');
    $table->decimal('admin_fee', 12, 2)->default(0)->after('price');
    $table->decimal('total_amount', 12, 2)->after('admin_fee');
    
    // Status terpisah: status order (pesanan) & status pembayaran
    $table->enum('payment_status', ['unpaid', 'pending', 'settlement', 'expired', 'failed', 'refunded'])
          ->default('unpaid')
          ->after('status');
          
    $table->string('payment_channel', 50)->nullable()->after('payment_status');
    $table->string('gateway_reference_id', 100)->nullable()->after('payment_channel');
    $table->string('payment_token', 255)->nullable()->after('gateway_reference_id');
    $table->text('payment_url')->nullable()->after('payment_token');
    
    $table->timestamp('paid_at')->nullable()->after('completed_at');
    $table->timestamp('expired_at')->nullable()->after('paid_at');
    $table->json('raw_response')->nullable()->after('expired_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
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
        ]);
        });
    }
};
