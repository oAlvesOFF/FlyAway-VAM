<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flight_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('active_flight_id')->index();
            $table->string('flight_number', 20);
            $table->decimal('latitude', 10, 6);
            $table->decimal('longitude', 10, 6);
            $table->integer('heading')->default(0);
            $table->integer('altitude')->default(0);
            $table->integer('ground_speed')->default(0);
            $table->string('phase', 20)->default('enroute');
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->foreign('active_flight_id')->references('id')->on('active_flights')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_positions');
    }
};
