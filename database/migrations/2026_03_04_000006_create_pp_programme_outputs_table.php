<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pp_programme_outputs', function (Blueprint $table) {
            $table->id();
            $table->string('output_code', 30)->unique();        // e.g. OUT-NEAT-Q4-2025
            $table->string('programme');                         // NEAT, Last Mile, Net Metering
            $table->string('period', 50);                        // Q4 2025, Oct-2024 to Dec-2025
            $table->unsignedInteger('connections_delivered')->nullable();
            $table->unsignedInteger('transformers_energised')->nullable();
            $table->unsignedInteger('jobs_pending_connection')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('programme');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pp_programme_outputs');
    }
};
