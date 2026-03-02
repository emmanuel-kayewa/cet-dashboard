<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('directorate_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['outage', 'safety', 'security', 'environmental', 'equipment_failure', 'operational', 'other'])->default('operational');
            $table->enum('severity', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->enum('status', ['reported', 'investigating', 'mitigating', 'resolved', 'closed'])->default('reported');
            $table->text('root_cause')->nullable();
            $table->text('resolution')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->string('affected_area')->nullable();
            $table->integer('affected_customers')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->enum('source', ['simulation', 'manual', 'oracle'])->default('manual');
            $table->text('notes')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['directorate_id', 'status']);
            $table->index(['severity', 'status']);
            $table->index('source');
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
