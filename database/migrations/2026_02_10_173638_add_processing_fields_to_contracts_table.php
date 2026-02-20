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
    Schema::table('contracts', function (Blueprint $table) {
        $table->enum('processing_status', ['queued','processing','done','failed'])
            ->default('queued')
            ->after('pdf_path');

        $table->unsignedTinyInteger('processing_progress')
            ->default(0)
            ->after('processing_status');

        $table->text('processing_error')->nullable()->after('processing_progress');
        $table->timestamp('processing_started_at')->nullable()->after('processing_error');
        $table->timestamp('processing_finished_at')->nullable()->after('processing_started_at');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            //
        });
    }
};