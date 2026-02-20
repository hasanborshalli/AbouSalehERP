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
        Schema::create('apartments', function (Blueprint $table) {
             $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->foreignId('floor_id')
                ->constrained('project_floors')
                ->cascadeOnDelete();

            $table->string('unit_number', 50); // "A1", "101", etc.

            $table->decimal('area_sqm', 10, 2)->nullable();
            $table->unsignedInteger('bedrooms')->nullable();
            $table->unsignedInteger('bathrooms')->nullable();
            $table->decimal('price_total', 12, 2)->nullable(); // selling price

            $table->enum('status', ['available', 'reserved', 'sold'])
                ->default('available');

            $table->string('notes')->nullable();

            $table->timestampsTz();

            $table->unique(['project_id', 'floor_id', 'unit_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};