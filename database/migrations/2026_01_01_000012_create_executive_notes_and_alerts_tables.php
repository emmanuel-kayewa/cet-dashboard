<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('executive_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('directorate_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->boolean('is_pinned')->default(false);
            $table->enum('visibility', ['private', 'directorate', 'all'])->default('private');
            $table->timestamps();

            $table->index(['directorate_id', 'is_pinned']);
        });

        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50); // kpi_threshold, risk_escalation, anomaly, system
            $table->string('severity', 20)->default('info'); // info, warning, critical
            $table->string('title');
            $table->text('message');
            $table->foreignId('directorate_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('metadata')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_dismissed')->default(false);
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'severity', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('executive_notes');
    }
};
