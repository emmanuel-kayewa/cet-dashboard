<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pp_weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('week_number');
            $table->unsignedSmallInteger('year');
            $table->date('report_date');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['week_number', 'year']);
            $table->index('year');
        });

        Schema::create('pp_report_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pp_weekly_report_id')->constrained('pp_weekly_reports')->cascadeOnDelete();
            $table->unsignedTinyInteger('section_number');
            $table->string('title');
            $table->string('section_type', 30); // completed_solar, construction_projects, transmission_projects, net_metering
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('pp_weekly_report_id');
        });

        Schema::create('pp_report_project_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pp_report_section_id')->constrained('pp_report_sections')->cascadeOnDelete();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('developer')->nullable();
            $table->decimal('size_mw', 10, 3)->nullable();
            $table->string('project_type')->nullable();
            $table->string('est_completion')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('pp_report_section_id');
        });

        Schema::create('pp_report_net_metering_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pp_report_section_id')->constrained('pp_report_sections')->cascadeOnDelete();
            $table->string('key_initiative');
            $table->string('status_value');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('pp_report_section_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pp_report_net_metering_entries');
        Schema::dropIfExists('pp_report_project_entries');
        Schema::dropIfExists('pp_report_sections');
        Schema::dropIfExists('pp_weekly_reports');
    }
};
