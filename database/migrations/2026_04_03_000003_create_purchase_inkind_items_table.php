<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_inkind_items', function (Blueprint $table) {
            $table->id();
            // Groups all in-kind payment items belonging to one purchase receipt
            $table->string('receipt_ref', 100)->index();
            $table->foreignId('inventory_item_id')->constrained('inventory_items');
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price_snapshot', 12, 2); // item price at time of transaction
            $table->decimal('total_value', 12, 2);
            $table->string('notes', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_inkind_items');
    }
};
