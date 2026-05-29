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
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('minimum_hours')->default(0);
            $table->string('image')->nullable();
            $table->text('allowed_categories')->nullable(); // stored as JSON-compatible text
            $table->timestamps();
        });

        Schema::create('aircraft', function (Blueprint $table) {
            $table->id();
            $table->string('registration')->unique();
            $table->string('icao');
            $table->string('name');
            $table->string('location', 4)->default('YSSY');
            $table->string('status')->default('active'); // active, maintenance, in_flight
            $table->string('category'); // e.g. B737, B777, C208
            $table->timestamps();
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('flight_number');
            $table->string('departure', 4);
            $table->string('arrival', 4);
            $table->text('route');
            $table->string('aircraft_type'); // Category or specific ICAO
            $table->decimal('flight_time', 5, 2); // hours
            $table->string('departure_time')->default('12:00');
            $table->integer('altitude')->default(30000);
            $table->timestamps();
        });

        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('aircraft_id')->constrained('aircraft')->onDelete('cascade');
            $table->longText('simbrief_ofp')->nullable(); // full JSON response
            $table->longText('simbrief_xml')->nullable(); // raw XML response for files
            $table->timestamps();
        });

        Schema::create('pireps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('flight_number');
            $table->string('departure', 4);
            $table->string('arrival', 4);
            $table->string('aircraft_registration');
            $table->string('aircraft_icao');
            $table->decimal('flight_time', 5, 2);
            $table->integer('landing_rate')->nullable()->default(null); // FPM (negative number usually)
            $table->integer('score')->default(100);
            $table->text('route')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->longText('log')->nullable(); // ACARS detailed step log
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pireps');
        Schema::dropIfExists('bids');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('aircraft');
        Schema::dropIfExists('ranks');
    }
};
