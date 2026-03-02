<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // kpi_update, financial_update, risk_change, outage, etc.
            $table->foreignId('directorate_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('data');
            $table->json('previous_data')->nullable();
            $table->string('status')->default('generated'); // generated, applied, discarded
            $table->timestamps();

            $table->index(['event_type', 'created_at']);
            $table->index('directorate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulation_logs');
    }
};
