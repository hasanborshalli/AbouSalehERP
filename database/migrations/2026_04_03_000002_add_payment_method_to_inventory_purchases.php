<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the existing enum to include 'in_kind'
        DB::statement("ALTER TABLE inventory_purchases MODIFY COLUMN payment_method ENUM('cash','bank','other','in_kind') DEFAULT 'cash'");
    }

    public function down(): void
    {
        // Revert: existing 'in_kind' rows become invalid — update them first
        DB::statement("UPDATE inventory_purchases SET payment_method = 'other' WHERE payment_method = 'in_kind'");
        DB::statement("ALTER TABLE inventory_purchases MODIFY COLUMN payment_method ENUM('cash','bank','other') DEFAULT 'cash'");
    }
};