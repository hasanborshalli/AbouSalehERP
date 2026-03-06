<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('managed_property_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('managed_property_id')->constrained()->cascadeOnDelete();

            $table->string('description');
            $table->string('category')->nullable(); // painting, plumbing, electrical, cleaning, other
            $table->decimal('amount', 14, 2);
            $table->date('expense_date');
            $table->string('vendor_name')->nullable();
            $table->text('notes')->nullable();

            // voiding
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('void_reason')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('managed_property_expenses');
    }
};
