<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('worker_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('scope_of_work');           // e.g. "Electricity wiring – Floor 1-3"
            $table->string('category')->nullable();    // e.g. "electrical", "plumbing", "carpentry"
            $table->date('contract_date');
            $table->date('start_date')->nullable();
            $table->date('expected_end_date')->nullable();
            $table->decimal('total_amount', 12, 2);    // e.g. 2000.00
            $table->integer('payment_months');         // e.g. 10
            $table->decimal('monthly_amount', 12, 2);  // e.g. 200.00
            $table->date('first_payment_date');
            $table->text('notes')->nullable();
            $table->string('pdf_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_contracts');
    }
};