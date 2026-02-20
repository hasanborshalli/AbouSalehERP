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
        Schema::create('invoices', function (Blueprint $table) {
             $table->id();

            $table->foreignId('contract_id')
                ->constrained('contracts')
                ->cascadeOnDelete();

            $table->string('invoice_number', 80)->unique();
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();

            $table->decimal('amount', 12, 2)->default(0);

            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])
                ->default('pending');

            $table->string('pdf_path')->nullable();      // tax invoice download
            $table->string('receipt_path')->nullable();  // proof of payment

            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};