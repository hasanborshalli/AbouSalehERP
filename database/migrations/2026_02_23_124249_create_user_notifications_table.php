<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
Schema::create('user_notifications', function (Blueprint $table) {
$table->id();

$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

// Unique key to prevent duplicates (idempotency)
$table->string('key', 191);

$table->string('type', 50); // invoice, progress, system
$table->string('title');
$table->text('message');

// Optional deep link (where to navigate when clicked)
$table->string('url')->nullable();

// Optional related entity
$table->string('entity_type')->nullable(); // invoice, contract, progress_item
$table->unsignedBigInteger('entity_id')->nullable();

$table->timestampTz('read_at')->nullable();
$table->timestampsTz();

$table->unique(['user_id', 'key']);
$table->index(['user_id', 'read_at']);
$table->index(['user_id', 'created_at']);
});
}

public function down(): void
{
Schema::dropIfExists('user_notifications');
}
};