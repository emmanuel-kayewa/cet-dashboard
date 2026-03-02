<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('directorate_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', ['operational', 'financial', 'strategic', 'compliance', 'technical', 'environmental', 'reputational']);
            $table->integer('likelihood')->default(1); // 1-5
            $table->integer('impact')->default(1); // 1-5
            $table->integer('risk_score')->storedAs('likelihood * impact'); // computed
            $table->enum('status', ['identified', 'assessed', 'mitigating', 'resolved', 'accepted'])->default('identified');
            $table->text('mitigation_plan')->nullable();
            $table->string('owner')->nullable();
            $table->date('review_date')->nullable();
            $table->enum('source', ['simulation', 'manual', 'oracle'])->default('manual');
            $table->text('notes')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['directorate_id', 'status']);
            $table->index('risk_score');
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risks');
    }
};
