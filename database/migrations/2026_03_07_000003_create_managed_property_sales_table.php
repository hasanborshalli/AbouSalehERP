<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('managed_property_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('managed_property_id')->constrained()->cascadeOnDelete();

            // Buyer info
            $table->string('buyer_name');
            $table->string('buyer_phone')->nullable();
            $table->string('buyer_email')->nullable();

            // Sale financials
            $table->decimal('sale_price', 14, 2);           // actual price sold
            $table->date('sale_date');

            // Owner payout
            $table->decimal('owner_payout_amount', 14, 2);  // = owner_asking_price
            $table->timestamp('owner_paid_at')->nullable();  // when we actually paid the owner

            // Company profit = sale_price - owner_payout - total_expenses
            // (computed on read, not stored to stay accurate)

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('managed_property_sales');
    }
};
