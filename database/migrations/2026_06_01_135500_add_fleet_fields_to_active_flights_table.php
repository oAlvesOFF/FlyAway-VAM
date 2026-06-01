<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('active_flights', function (Blueprint $table) {
            $table->foreignId('airline_id')->nullable()->constrained('airlines')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('active_flights', function (Blueprint $table) {
            $table->dropForeign(['airline_id']);
            $table->dropColumn('airline_id');
        });
    }
};
