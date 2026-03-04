<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_contracts', function (Blueprint $table) {
            // Add project_ids if not already there (previous migration may have added it)
            if (!Schema::hasColumn('worker_contracts', 'project_ids')) {
                $table->json('project_ids')->nullable()->after('project_id');
            }
            if (!Schema::hasColumn('worker_contracts', 'project_costs')) {
                $table->json('project_costs')->nullable()->after('project_ids')
                      ->comment('Map of project_id => cost amount');
            }
            if (!Schema::hasColumn('worker_contracts', 'apartment_id')) {
                $table->foreignId('apartment_id')->nullable()->constrained('apartments')->nullOnDelete();
            }
            if (!Schema::hasColumn('worker_contracts', 'apartment_ids')) {
                $table->json('apartment_ids')->nullable();
            }
            if (!Schema::hasColumn('worker_contracts', 'apartment_costs')) {
                $table->json('apartment_costs')->nullable()
                      ->comment('Map of apartment_id => cost amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('worker_contracts', function (Blueprint $table) {
            $cols = ['project_ids','project_costs','apartment_costs','apartment_ids'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('worker_contracts', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('worker_contracts', 'apartment_id')) {
                $table->dropForeign(['apartment_id']);
                $table->dropColumn('apartment_id');
            }
        });
    }
};