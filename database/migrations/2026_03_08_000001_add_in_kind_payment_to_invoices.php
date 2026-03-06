<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add payment_type to CONTRACTS ('cash' or 'in_kind')
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('payment_type', 20)->default('cash')->after('notes');
            $table->text('in_kind_notes')->nullable()->after('payment_type');
        });

        // 2. in_kind_payments: one record per in-kind transaction
        //    - contract_id always set (Option 1: full contract, Option 2: which contract)
        //    - invoice_id nullable (Option 2 only: linked to a specific invoice)
        Schema::create('in_kind_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->date('payment_date');
            $table->decimal('total_estimated_value', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('contract_id')->references('id')->on('contracts')->cascadeOnDelete();
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
        });

        // 3. in_kind_payment_items: the individual inventory items received
        Schema::create('in_kind_payment_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('in_kind_payment_id');
            $table->unsignedBigInteger('inventory_item_id');
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price_snapshot', 14, 2);  // price at time of receipt
            $table->decimal('total_value', 14, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('in_kind_payment_id')->references('id')->on('in_kind_payments')->cascadeOnDelete();
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->cascadeOnDelete();
        });

        // 4. Drop the wrong table from previous session (if it was migrated)
        Schema::dropIfExists('invoice_inventory_payments');

        // 5. Drop wrong payment_type column on invoices if it exists
        if (Schema::hasColumn('invoices', 'payment_type')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('payment_type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('in_kind_payment_items');
        Schema::dropIfExists('in_kind_payments');

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'in_kind_notes']);
        });
    }
};