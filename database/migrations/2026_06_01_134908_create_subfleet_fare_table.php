<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subfleet_fare', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subfleet_id')->constrained('subfleets')->cascadeOnDelete();
            $table->foreignId('fare_id')->constrained('fares')->cascadeOnDelete();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->integer('capacity')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subfleet_fare');
    }
};
