<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('managed_properties', function (Blueprint $table) {
            $table->id();

            // Owner info
            $table->string('owner_name');
            $table->string('owner_phone');
            $table->string('owner_email')->nullable();

            // Property details
            $table->string('address');
            $table->string('city')->nullable();
            $table->string('area')->nullable();          // neighborhood
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->decimal('area_sqm', 10, 2)->nullable();
            $table->text('description')->nullable();

            // Service type
            $table->enum('type', ['flip', 'rental']);   // flip = buy/sell, rental = rent management

            // Status
            $table->enum('status', ['pending', 'active', 'sold', 'rented', 'terminated'])
                  ->default('pending');

            // Financials
            $table->decimal('owner_asking_price', 14, 2);           // what owner expects back
            $table->decimal('estimated_renovation_cost', 14, 2)->default(0);

            // flip: the price we plan to sell at
            $table->decimal('agreed_listing_price', 14, 2)->nullable();

            // rental: monthly rent amount to collect from tenant
            $table->decimal('agreed_rent_price', 14, 2)->nullable();

            // rental: % of monthly rent the company keeps as commission
            $table->decimal('company_commission_pct', 6, 3)->nullable(); // e.g. 10.000 = 10%

            $table->date('agreement_date');
            $table->text('notes')->nullable();
            $table->string('pdf_path')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('managed_properties');
    }
};
