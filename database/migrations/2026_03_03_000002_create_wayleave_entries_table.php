<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wayleave_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('directorate_id')->constrained()->cascadeOnDelete();
            $table->string('category', 20); // wayleave | survey
            $table->string('aspect');

            $table->unsignedInteger('received')->default(0);
            $table->unsignedInteger('cleared')->default(0);

            // Snapshot date for the figures being reported
            $table->date('report_date');

            $table->text('notes')->nullable();
            $table->string('source', 20)->default('manual');
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['directorate_id', 'category', 'report_date']);
            $table->unique(['directorate_id', 'category', 'report_date', 'aspect'], 'wl_entries_dir_cat_date_aspect_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wayleave_entries');
    }
};
