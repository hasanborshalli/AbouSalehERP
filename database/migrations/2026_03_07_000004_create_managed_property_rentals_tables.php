<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Rental contracts (each tenant staying)
        Schema::create('managed_property_rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('managed_property_id')->constrained()->cascadeOnDelete();

            // Tenant info
            $table->string('tenant_name');
            $table->string('tenant_phone')->nullable();
            $table->string('tenant_email')->nullable();

            // Financials
            $table->decimal('monthly_rent', 14, 2);          // collected from tenant
            $table->decimal('owner_monthly_share', 14, 2);   // paid to owner each month
            $table->decimal('company_monthly_commission', 14, 2); // company keeps

            $table->decimal('deposit_amount', 14, 2)->default(0);
            $table->timestamp('deposit_returned_at')->nullable();

            $table->date('start_date');
            $table->date('end_date');                         // contract end (not necessarily actual end)
            $table->date('actual_end_date')->nullable();      // set when terminated early

            $table->enum('status', ['active', 'ended', 'terminated'])->default('active');
            $table->text('notes')->nullable();
            $table->string('pdf_path')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Monthly rent payment records
        Schema::create('managed_property_rental_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('managed_property_rental_id');
            $table->foreign('managed_property_rental_id', 'mp_rental_pmts_rental_fk')
                  ->references('id')
                  ->on('managed_property_rentals')
                  ->cascadeOnDelete();

            $table->date('due_date');                            // when rent is due
            $table->decimal('amount_due', 14, 2);               // monthly_rent at time of creation
            $table->decimal('owner_share', 14, 2);
            $table->decimal('company_commission', 14, 2);

            // Step 1: tenant pays company
            $table->decimal('amount_collected', 14, 2)->nullable();
            $table->timestamp('collected_at')->nullable();

            // Step 2: company pays owner
            $table->decimal('owner_paid_amount', 14, 2)->nullable();
            $table->timestamp('owner_paid_at')->nullable();

            $table->enum('status', ['pending', 'collected', 'owner_paid'])->default('pending');
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('managed_property_rental_payments');
        Schema::dropIfExists('managed_property_rentals');
    }
};
