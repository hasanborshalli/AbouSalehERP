<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('worker_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_contract_id')->constrained('worker_contracts')->cascadeOnDelete();
            $table->string('payment_number');          // e.g. "WP-00001-1"
            $table->integer('installment_index');       // 1, 2, 3 ...
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->string('receipt_path')->nullable();
            $table->foreignId('marked_paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_payments');
    }
};