<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subfleets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id')->nullable()->constrained('airlines')->nullOnDelete();
            $table->foreignId('hub_id')->nullable()->constrained('airports')->nullOnDelete();
            $table->string('type');
            $table->string('simbrief_type')->nullable();
            $table->string('name');
            $table->integer('fuel_type')->nullable();
            $table->decimal('cost_block_hour', 10, 2)->nullable();
            $table->decimal('cost_delay_minute', 10, 2)->nullable();
            $table->decimal('ground_handling_multiplier', 5, 2)->nullable();
            $table->decimal('cargo_capacity', 10, 2)->nullable();
            $table->decimal('fuel_capacity', 10, 2)->nullable();
            $table->decimal('gross_weight', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subfleets');
    }
};
