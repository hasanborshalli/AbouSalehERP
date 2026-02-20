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
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();

            $table->dateTime('posted_at'); // when cash movement happened
            $table->foreignId('account_id')->constrained('accounts');

            // cash-basis: you can represent movements as + (in) and - (out)
            $table->decimal('amount', 14, 2); // positive number
            $table->enum('direction', ['in', 'out']); // in = revenue/cash-in, out = expense/cash-out

            $table->string('description')->nullable();

            // polymorphic link (invoice, inventory_purchase, operating_expense)
            $table->string('source_type')->nullable();
            $table->string('source_id')->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestampsTz();

            $table->index(['posted_at']);
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};