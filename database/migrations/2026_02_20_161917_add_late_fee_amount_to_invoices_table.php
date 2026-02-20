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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('late_fee_amount', 12, 2)->default(0)->after('amount');
            $table->timestamp('late_marked_at')->nullable()->after('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['late_fee_amount', 'late_marked_at']);
        });
    }
};