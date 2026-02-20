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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();

            // client user
            $table->foreignId('client_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->foreignId('apartment_id')
                ->constrained('apartments')
                ->cascadeOnDelete();


            $table->date('contract_date')->nullable();

            $table->decimal('total_price', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->nullable();
            $table->decimal('final_price', 12, 2)->default(0);

            $table->decimal('down_payment', 12, 2)->default(0);

            // installment plan
            $table->unsignedInteger('installment_months')->default(0);
            $table->decimal('installment_amount', 12, 2)->default(0);

            $table->enum('status', ['draft', 'signed', 'active', 'completed', 'cancelled'])
                ->default('draft');

            // pdf path for "download pdf"
            $table->string('pdf_path')->nullable();

            // created by admin
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestampsTz();

            // one apartment cannot be sold twice (use 1 active contract only)
            $table->unique(['apartment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};