<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_in_kind_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('worker_payment_id');
            $table->unsignedBigInteger('worker_contract_id');
            $table->date('payment_date');
            $table->decimal('total_estimated_value', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('worker_payment_id')->references('id')->on('worker_payments')->cascadeOnDelete();
            $table->foreign('worker_contract_id')->references('id')->on('worker_contracts')->cascadeOnDelete();
        });

        Schema::create('worker_in_kind_payment_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('worker_in_kind_payment_id');
            $table->unsignedBigInteger('inventory_item_id');
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price_snapshot', 14, 2);
            $table->decimal('total_value', 14, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('worker_in_kind_payment_id')->references('id')->on('worker_in_kind_payments')->cascadeOnDelete();
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_in_kind_payment_items');
        Schema::dropIfExists('worker_in_kind_payments');
    }
};