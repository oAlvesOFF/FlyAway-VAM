<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RankSeeder::class,
            AircraftSeeder::class,
            ScheduleSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@atlanticstar.aero',
            'password' => bcrypt('password'),
            'pilot_id' => 'ASR0001',
            'is_admin' => true,
            'total_hours' => 5200,
        ]);

        User::factory()->create([
            'name' => 'Test Pilot',
            'email' => 'pilot@atlanticstar.aero',
            'password' => bcrypt('password'),
            'pilot_id' => 'ASR1001',
            'is_admin' => false,
            'total_hours' => 250,
        ]);

        $this->call([
            RolePermissionSeeder::class,
            AchievementSeeder::class,
            PirepSeeder::class,
            BidSeeder::class,
            ActiveFlightSeeder::class,
            NewsSeeder::class,
        ]);
    }
}
