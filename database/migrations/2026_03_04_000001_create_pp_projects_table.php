<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pp_projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code', 20)->unique();    // e.g. GEN-001, TRN-001, DST-001
            $table->string('project_name');
            $table->string('sector', 30);                     // Generation, Transmission, Distribution, IPP
            $table->string('sub_sector')->nullable();          // Utility Scale Solar, Green Cities Solar, etc.
            $table->string('status', 30)->default('Preparation'); // Execution, Preparation, Completed, Cancelled
            $table->string('programme')->nullable();           // Renewables, Green Cities, Transmission Planning, etc.
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('contractor')->nullable();
            $table->string('developer')->nullable();
            $table->string('funder')->nullable();
            $table->string('funding_type', 30)->nullable();    // BOT, Grant, etc.
            $table->decimal('cost_usd', 18, 2)->nullable();
            $table->decimal('cost_zmw', 18, 2)->nullable();
            $table->decimal('capacity_mw', 10, 3)->nullable();
            $table->decimal('progress_pct', 5, 2)->nullable();
            $table->date('cod_planned')->nullable();           // Commercial Operation Date
            $table->text('key_issue_summary')->nullable();
            $table->date('last_update_date')->nullable();
            $table->string('rag_status', 10)->default('Amber'); // Red, Amber, Green
            $table->text('notes')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('sector');
            $table->index('status');
            $table->index('rag_status');
            $table->index(['sector', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pp_projects');
    }
};
