<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add payment_type to invoices (default 'cash')
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('payment_type', 20)->default('cash')->after('status');
        });

        // 2. New table: tracks which inventory items were used to pay an invoice
        Schema::create('invoice_inventory_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('inventory_item_id');
            $table->decimal('quantity_used', 12, 3);
            $table->decimal('unit_price', 14, 2);   // snapshot of item price at time of payment
            $table->decimal('total_value', 14, 2);  // quantity_used × unit_price
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')
                  ->references('id')->on('invoices')
                  ->cascadeOnDelete();

            $table->foreign('inventory_item_id')
                  ->references('id')->on('inventory_items')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_inventory_payments');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });
    }
};
