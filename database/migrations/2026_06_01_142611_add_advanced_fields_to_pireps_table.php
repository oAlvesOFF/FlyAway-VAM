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
        Schema::table('pireps', function (Blueprint $table) {
            $table->unsignedInteger('block_time')->nullable()->after('flight_time');
            $table->unsignedInteger('planned_flight_time')->nullable()->after('block_time');
            $table->decimal('zfw', 10, 2)->nullable()->after('planned_flight_time');
            $table->decimal('block_fuel', 10, 2)->nullable()->after('zfw');
            $table->decimal('fuel_used', 10, 2)->nullable()->after('block_fuel');
            $table->tinyInteger('source')->default(0)->after('status');
            $table->tinyInteger('state')->default(0)->after('source');
            $table->string('flight_type')->nullable()->after('state');
            $table->dateTime('block_off_time')->nullable()->after('submitted_at');
            $table->dateTime('block_on_time')->nullable()->after('block_off_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pireps', function (Blueprint $table) {
            $table->dropColumn([
                'block_time',
                'planned_flight_time',
                'zfw',
                'block_fuel',
                'fuel_used',
                'source',
                'state',
                'flight_type',
                'block_off_time',
                'block_on_time'
            ]);
        });
    }
};
