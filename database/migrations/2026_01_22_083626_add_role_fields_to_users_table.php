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
        Schema::table('users', function (Blueprint $table) {
            // roles: owner | admin | client
            $table->string('role', 20)->default('admin')->after('password');

            $table->string('phone', 30)->nullable()->after('email');
            $table->string('name')->nullable()->change(); // allow null if you want to create quickly then fill later

            // employee status
            $table->boolean('is_active')->default(true)->after('phone');

            // who created this user (admin creates clients/employees)
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['role', 'phone', 'is_active', 'created_by']);
            // name change is not reverted here (safe)
        });
    }
};