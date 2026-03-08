<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('worker_contracts', function (Blueprint $table) {
            $table->json('managed_property_ids')->nullable()->after('apartment_costs')
                ->comment('Array of managed property IDs');
            $table->json('managed_property_costs')->nullable()->after('managed_property_ids')
                ->comment('Map of managed_property_id => cost');
        });
    }

    public function down(): void
    {
        Schema::table('worker_contracts', function (Blueprint $table) {
            $table->dropColumn(['managed_property_ids', 'managed_property_costs']);
        });
    }
};
