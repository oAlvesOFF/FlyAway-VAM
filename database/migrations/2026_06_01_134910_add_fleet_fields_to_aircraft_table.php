<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aircraft', function (Blueprint $table) {
            $table->foreignId('subfleet_id')->nullable()->constrained('subfleets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('aircraft', function (Blueprint $table) {
            $table->dropForeign(['subfleet_id']);
            $table->dropColumn('subfleet_id');
        });
    }
};
