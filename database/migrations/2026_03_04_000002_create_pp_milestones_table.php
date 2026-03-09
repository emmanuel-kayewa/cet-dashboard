<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pp_milestones', function (Blueprint $table) {
            $table->id();
            $table->string('milestone_code', 30)->unique();   // e.g. MS-GEN-001
            $table->foreignId('pp_project_id')->constrained('pp_projects')->cascadeOnDelete();
            $table->string('milestone');                       // Description of the milestone
            $table->date('actual_date')->nullable();
            $table->string('status', 30)->default('Pending');  // Completed, In Progress, Pending
            $table->text('notes')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('pp_project_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pp_milestones');
    }
};
