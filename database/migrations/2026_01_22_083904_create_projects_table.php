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
        Schema::create('projects', function (Blueprint $table) {
             $table->id();

            $table->string('name');
            $table->string('code', 60)->unique()->nullable(); // optional code
            $table->string('city')->nullable();
             $table->string('area')->nullable();
              $table->string('address')->nullable();
            $table->text('notes')->nullable();

            $table->date('start_date')->nullable();
            $table->date('estimated_completion_date')->nullable();

            $table->enum('status', ['planned', 'in_progress', 'completed', 'on_hold'])
                ->default('planned');

            // project manager (employee)
            $table->foreignId('manager_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};