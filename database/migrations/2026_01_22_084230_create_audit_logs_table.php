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
        Schema::create('audit_logs', function (Blueprint $table) {
             $table->id();

            // who did it
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('event', 60); // created/updated/deleted/login/password_reset/etc
            $table->string('entity_type', 80)->nullable(); // e.g. Contract, InventoryItem, User

            $table->text('details')->nullable(); // which field changed
            $table->timestampsTz();

            $table->index(['entity_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};