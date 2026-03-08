<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contract_progress_items', function (Blueprint $table) {
            // Make contract_id nullable so progress can exist without a contract
            $table->foreignId('apartment_id')
                ->nullable()
                ->after('contract_id')
                ->constrained('apartments')
                ->nullOnDelete();
        });

        // Allow contract_id to be nullable
        Schema::table('contract_progress_items', function (Blueprint $table) {
            $table->foreignId('contract_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('contract_progress_items', function (Blueprint $table) {
            $table->dropForeign(['apartment_id']);
            $table->dropColumn('apartment_id');
            $table->foreignId('contract_id')->nullable(false)->change();
        });
    }
};
