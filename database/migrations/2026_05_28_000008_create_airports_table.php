<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('airports', function (Blueprint $table) {
            $table->id();
            $table->string('icao', 4)->unique();
            $table->string('name');
            $table->string('city');
            $table->string('country');
            $table->decimal('lat', 10, 6);
            $table->decimal('lng', 10, 6);
            $table->integer('elevation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('airports');
    }
};
