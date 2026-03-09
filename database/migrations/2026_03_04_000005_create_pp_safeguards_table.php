<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pp_safeguards', function (Blueprint $table) {
            $table->id();
            $table->string('record_code', 30)->unique();       // e.g. SG-PORT-Q4-2025
            $table->string('scope');                            // Portfolio (Q4 2025), LTDRP (Main), etc.
            $table->foreignId('pp_project_id')->nullable()->constrained('pp_projects')->cascadeOnDelete();
            $table->unsignedInteger('wayleave_received')->nullable();
            $table->unsignedInteger('wayleave_cleared')->nullable();
            $table->unsignedInteger('wayleave_pending')->nullable();
            $table->unsignedInteger('survey_received')->nullable();
            $table->unsignedInteger('survey_cleared')->nullable();
            $table->unsignedInteger('survey_pending')->nullable();
            $table->unsignedInteger('paps')->nullable();        // Project Affected Persons
            $table->decimal('comp_paid_zmw', 18, 2)->nullable();
            $table->string('report_period', 30)->nullable();    // e.g. Q4 2025
            $table->text('notes')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('pp_project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pp_safeguards');
    }
};
