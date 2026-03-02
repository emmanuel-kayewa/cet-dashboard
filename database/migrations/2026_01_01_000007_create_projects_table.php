<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('directorate_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'on_hold', 'completed', 'cancelled'])->default('planned');
            $table->decimal('budget', 18, 2)->nullable();
            $table->decimal('spent', 18, 2)->default(0);
            $table->integer('completion_percentage')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->string('priority')->default('medium'); // low, medium, high, critical
            $table->string('project_manager')->nullable();
            $table->text('milestones')->nullable(); // JSON
            $table->enum('source', ['simulation', 'manual', 'oracle'])->default('manual');
            $table->text('notes')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['directorate_id', 'status']);
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
