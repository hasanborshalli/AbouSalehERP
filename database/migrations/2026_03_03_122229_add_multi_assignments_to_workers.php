<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_contracts', function (Blueprint $table) {
            // Multi-project and multi-apartment support
            // The original project_id is kept for backward compat
            $table->json('project_ids')->nullable()->after('project_id')
                  ->comment('Array of project IDs (multi-select)');
            $table->foreignId('apartment_id')->nullable()->constrained('apartments')
                  ->nullOnDelete()->after('project_ids');
            $table->json('apartment_ids')->nullable()->after('apartment_id')
                  ->comment('Array of apartment IDs (multi-select)');
        });
    }

    public function down(): void
    {
        Schema::table('worker_contracts', function (Blueprint $table) {
            $table->dropForeign(['apartment_id']);
            $table->dropColumn(['project_ids', 'apartment_id', 'apartment_ids']);
        });
    }
};