<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('airlines', function (Blueprint $table) {
            $table->id();
            $table->string('icao', 5);
            $table->string('iata', 5)->nullable();
            $table->string('name');
            $table->string('callsign')->nullable();
            $table->string('logo')->nullable();
            $table->string('country')->nullable();
            $table->unsignedBigInteger('total_flights')->default(0);
            $table->unsignedBigInteger('total_time')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('airlines');
    }
};
