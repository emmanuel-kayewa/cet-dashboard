<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpis', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category'); // financial, operational, strategic, risk, hr, customer, project, technical
            $table->string('unit')->default('number'); // number, percentage, currency, ratio
            $table->string('currency_code', 3)->nullable(); // ZMW, USD
            $table->decimal('target_value', 15, 2)->nullable();
            $table->decimal('warning_threshold', 15, 2)->nullable();
            $table->decimal('critical_threshold', 15, 2)->nullable();
            $table->enum('trend_direction', ['up_is_good', 'down_is_good', 'neutral'])->default('up_is_good');
            $table->boolean('is_global')->default(false); // true = org-wide, false = directorate-specific
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });

        // Pivot table: which KPIs belong to which directorates
        Schema::create('directorate_kpi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('directorate_id')->constrained()->onDelete('cascade');
            $table->foreignId('kpi_id')->constrained()->onDelete('cascade');
            $table->decimal('custom_target', 15, 2)->nullable();
            $table->timestamps();

            $table->unique(['directorate_id', 'kpi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('directorate_kpi');
        Schema::dropIfExists('kpis');
    }
};
