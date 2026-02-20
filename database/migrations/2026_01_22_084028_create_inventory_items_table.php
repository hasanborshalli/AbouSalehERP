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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->enum('type', ['internal', 'external', 'sale'])->default('internal');

            $table->decimal('price', 12, 2)->default(0);       // item price
            $table->unsignedInteger('quantity')->default(0);   // stock qty

            $table->boolean('is_out_of_stock')->default(false);

            $table->string('image_path')->nullable();

            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};