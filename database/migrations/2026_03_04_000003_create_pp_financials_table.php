<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pp_financials', function (Blueprint $table) {
            $table->id();
            $table->string('finance_code', 30)->unique();     // e.g. FIN-GEN-001-USD
            $table->foreignId('pp_project_id')->nullable()->constrained('pp_projects')->cascadeOnDelete();
            $table->date('as_of_date');
            $table->decimal('committed_amount', 18, 2)->nullable();
            $table->decimal('paid_to_date', 18, 2)->nullable();
            $table->string('currency', 3)->default('USD');     // USD, ZMW
            $table->text('notes')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('pp_project_id');
            $table->index('currency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pp_financials');
    }
};
