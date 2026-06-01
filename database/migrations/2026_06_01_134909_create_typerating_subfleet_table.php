<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('typerating_subfleet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('typerating_id')->constrained('typeratings')->cascadeOnDelete();
            $table->foreignId('subfleet_id')->constrained('subfleets')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('typerating_subfleet');
    }
};
