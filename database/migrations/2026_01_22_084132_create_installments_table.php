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
        Schema::create('installments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contract_id')
                ->constrained('contracts')
                ->cascadeOnDelete();

            $table->unsignedInteger('sequence_no'); // 1..N
            $table->date('due_date')->nullable();

            // milestone label (from your notes)
            $table->string('milestone')->nullable(); // "Foundation Pouring", etc.

            $table->decimal('amount_due', 12, 2)->default(0);

            $table->enum('status', ['pending', 'paid', 'overdue'])
                ->default('pending');

            // actual payment fields
            $table->dateTime('paid_at')->nullable();
            $table->decimal('amount_paid', 12, 2)->nullable();

            $table->string('payment_method', 50)->nullable(); // cash, bank, whish...
            $table->string('receipt_path')->nullable();

            // who recorded payment
            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestampsTz();

            $table->unique(['contract_id', 'sequence_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};