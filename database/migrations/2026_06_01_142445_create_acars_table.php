<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('pirep_id')->index();
            $table->tinyInteger('type')->default(0);
            $table->tinyInteger('nav_type')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->string('name')->nullable();
            $table->string('status')->nullable();
            $table->string('log')->nullable();
            $table->decimal('lat', 10, 6)->nullable();
            $table->decimal('lon', 10, 6)->nullable();
            $table->unsignedInteger('distance')->nullable();
            $table->unsignedInteger('heading')->nullable();
            $table->unsignedInteger('altitude')->nullable();
            $table->unsignedInteger('altitude_agl')->nullable();
            $table->unsignedInteger('altitude_msl')->nullable();
            $table->integer('vs')->nullable();
            $table->unsignedInteger('gs')->nullable();
            $table->unsignedInteger('ias')->nullable();
            $table->unsignedInteger('transponder')->nullable();
            $table->string('autopilot')->nullable();
            $table->decimal('fuel_flow', 8, 2)->nullable();
            $table->dateTime('sim_time')->nullable();
            $table->tinyInteger('source')->default(0);
            $table->timestamps();

            $table->foreign('pirep_id')->references('id')->on('pireps')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acars');
    }
};
