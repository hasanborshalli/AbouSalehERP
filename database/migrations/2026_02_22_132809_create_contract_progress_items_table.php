<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
Schema::create('contract_progress_items', function (Blueprint $table) {
$table->id();
$table->foreignId('contract_id')->constrained()->cascadeOnDelete();

$table->string('title'); // Electricity wiring, Plumbing, ...
$table->text('description')->nullable();
$table->unsignedInteger('sort_order')->default(0);

$table->enum('status', ['todo', 'in_progress', 'done'])->default('todo');

// Optional: weight-based overall progress
$table->unsignedTinyInteger('weight')->default(10); // 1..100 (sum doesn't have to be 100)

$table->date('started_at')->nullable();
$table->date('completed_at')->nullable();

$table->timestamps();

$table->index(['contract_id', 'sort_order']);
$table->index(['contract_id', 'status']);
});
}

public function down(): void
{
Schema::dropIfExists('contract_progress_items');
}
};