<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('active_flights', function (Blueprint $table) {
            $table->id();
            $table->string('flight_number');
            $table->string('aircraft_registration');
            $table->string('aircraft_icao', 4);
            $table->string('aircraft_type', 100);
            $table->string('departure', 4);
            $table->string('arrival', 4);
            $table->decimal('departure_lat', 10, 6)->nullable();
            $table->decimal('departure_lng', 10, 6)->nullable();
            $table->decimal('arrival_lat', 10, 6)->nullable();
            $table->decimal('arrival_lng', 10, 6)->nullable();
            $table->decimal('current_lat', 10, 6)->nullable();
            $table->decimal('current_lng', 10, 6)->nullable();
            $table->integer('heading')->default(0);
            $table->integer('altitude')->default(0);
            $table->integer('ground_speed')->default(0);
            $table->string('phase')->default('preflight');
            $table->string('status')->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('position_updated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('active_flights');
    }
};
