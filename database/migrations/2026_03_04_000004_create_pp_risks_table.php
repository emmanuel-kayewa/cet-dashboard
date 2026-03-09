<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pp_risks', function (Blueprint $table) {
            $table->id();
            $table->string('risk_code', 20)->unique();         // e.g. R-001
            $table->foreignId('pp_project_id')->nullable()->constrained('pp_projects')->cascadeOnDelete();
            $table->string('risk_category');                    // Wayleave/Compensation, Procurement/Approvals, Data/Governance
            $table->text('risk_description');
            $table->unsignedTinyInteger('likelihood');           // 1-5
            $table->unsignedTinyInteger('impact');               // 1-5
            $table->unsignedTinyInteger('severity');             // likelihood * impact
            $table->string('risk_level', 10);                   // Red, Amber, Green
            $table->text('mitigation')->nullable();
            $table->string('owner')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('Open');      // Open, Mitigating, Closed
            $table->text('notes')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('pp_project_id');
            $table->index('risk_level');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pp_risks');
    }
};
