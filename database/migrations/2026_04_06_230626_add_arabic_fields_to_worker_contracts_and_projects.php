<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_contracts', function (Blueprint $table) {
            $table->string('scope_of_work_ar', 500)->nullable()->after('scope_of_work');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('name_ar', 255)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('worker_contracts', function (Blueprint $table) {
            $table->dropColumn('scope_of_work_ar');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('name_ar');
        });
    }
};
