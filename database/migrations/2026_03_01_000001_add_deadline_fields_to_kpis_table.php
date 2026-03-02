<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpis', function (Blueprint $table) {
            $table->date('target_deadline')->nullable()->after('critical_threshold');
            $table->enum('target_period_type', ['monthly', 'quarterly', 'annual'])->nullable()->after('target_deadline');
            $table->json('milestone_targets')->nullable()->after('target_period_type');
            // e.g. [{"date": "2026-03-31", "target": 80}, {"date": "2026-06-30", "target": 90}]
        });
    }

    public function down(): void
    {
        Schema::table('kpis', function (Blueprint $table) {
            $table->dropColumn(['target_deadline', 'target_period_type', 'milestone_targets']);
        });
    }
};
