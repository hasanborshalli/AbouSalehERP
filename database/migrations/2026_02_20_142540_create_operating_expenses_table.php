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
        Schema::create('operating_expenses', function (Blueprint $table) {
            $table->id();

            $table->date('expense_date');
            $table->string('category'); // rent, salary, fuel, utilities...
            $table->decimal('amount', 14, 2);
            $table->string('description')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestampsTz();
            $table->index(['expense_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operating_expenses');
    }
};