<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('icon')->nullable();
            $table->string('category')->default('general');
            $table->unsignedInteger('threshold')->default(0);
            $table->string('metric'); // total_flights, total_hours, perfect_landings, etc.
            $table->timestamps();
        });

        Schema::create('achievement_user', function (Blueprint $table) {
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('unlocked_at');
            $table->timestamps();
            $table->primary(['achievement_id', 'user_id']);
        });

        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category');
            $table->json('waypoints')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tour_user', function (Blueprint $table) {
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('progress')->default(0);
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->primary(['tour_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_user');
        Schema::dropIfExists('tours');
        Schema::dropIfExists('achievement_user');
        Schema::dropIfExists('achievements');
    }
};
