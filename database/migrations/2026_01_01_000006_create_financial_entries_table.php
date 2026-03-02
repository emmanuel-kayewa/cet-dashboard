<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('directorate_id')->constrained()->onDelete('cascade');
            $table->string('category'); // revenue, expense, budget, capex, opex
            $table->string('sub_category')->nullable();
            $table->string('description');
            $table->decimal('amount', 18, 2);
            $table->decimal('budgeted_amount', 18, 2)->nullable();
            $table->string('currency', 3)->default('ZMW');
            $table->date('period_date');
            $table->enum('period_type', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->enum('source', ['simulation', 'manual', 'oracle'])->default('manual');
            $table->text('notes')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['directorate_id', 'category', 'period_date']);
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_entries');
    }
};
