<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_id')->constrained()->onDelete('cascade');
            $table->foreignId('directorate_id')->constrained()->onDelete('cascade');
            $table->decimal('value', 15, 2);
            $table->decimal('previous_value', 15, 2)->nullable();
            $table->date('period_date');
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->enum('source', ['simulation', 'manual', 'oracle'])->default('manual');
            $table->text('notes')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['kpi_id', 'directorate_id', 'period_date']);
            $table->index(['period_date', 'period_type']);
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_entries');
    }
};
