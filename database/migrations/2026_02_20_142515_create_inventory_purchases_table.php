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
        Schema::create('inventory_purchases', function (Blueprint $table) {
             $table->id();

            $table->foreignId('inventory_item_id')->constrained('inventory_items')->nullOnDelete();

            $table->date('purchase_date');
            $table->unsignedInteger('qty');
            $table->decimal('unit_cost', 14, 2);
            $table->decimal('total_cost', 14, 2);

            $table->string('vendor_name')->nullable();
            $table->string('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestampsTz();
            $table->index(['purchase_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_purchases');
    }
};