<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('simbriefs', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('flight_id', 36)->nullable();
            $table->unsignedBigInteger('pirep_id')->nullable();
            $table->mediumText('acars_xml')->nullable();
            $table->mediumText('ofp_xml')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'flight_id']);
            $table->index('pirep_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simbriefs');
    }
};
