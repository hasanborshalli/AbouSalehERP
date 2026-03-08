<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Track client-requested material upgrades
        Schema::create('client_material_upgrades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();

            // The original material being replaced (if it existed)
            $table->foreignId('old_inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->decimal('old_quantity', 12, 3)->nullable();

            // The new upgraded material
            $table->foreignId('new_inventory_item_id')->constrained('inventory_items');
            $table->decimal('new_quantity', 12, 3);
            $table->decimal('unit_price_snapshot', 12, 2);
            $table->decimal('total_amount', 12, 2);

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_material_upgrades');
    }
};
