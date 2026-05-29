<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('active_flights', function (Blueprint $table) {
            $table->timestamp('ended_at')->nullable()->after('position_updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('active_flights', function (Blueprint $table) {
            $table->dropColumn('ended_at');
        });
    }
};
