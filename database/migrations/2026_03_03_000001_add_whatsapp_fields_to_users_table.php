<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('whatsapp_phone')->nullable()->after('preferences');
            $table->boolean('whatsapp_opt_in')->default(false)->after('whatsapp_phone');

            $table->index('whatsapp_opt_in');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['whatsapp_opt_in']);
            $table->dropColumn(['whatsapp_opt_in', 'whatsapp_phone']);
        });
    }
};
