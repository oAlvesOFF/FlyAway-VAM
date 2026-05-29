<?php

namespace Database\Seeders;

use App\Models\Pirep;
use Illuminate\Database\Seeder;

class PirepSeeder extends Seeder
{
    public function run(): void
    {
        $pireps = [
            [
                'user_id' => 2,
                'flight_number' => 'ASR101',
                'departure' => 'YSSY',
                'arrival' => 'YMML',
                'aircraft_registration' => 'VH-NXC',
                'aircraft_icao' => 'B738',
                'flight_time' => 1.5,
                'landing_rate' => -185,
                'route' => 'SYD RIC H66 ML',
                'log' => 'Smooth flight, light chop at FL340. Visual approach RWY 16.',
                'status' => 'approved',
                'submitted_at' => now()->subDays(2),
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => 2,
                'flight_number' => 'ASR401',
                'departure' => 'YSSY',
                'arrival' => 'YBCS',
                'aircraft_registration' => 'VH-NXA',
                'aircraft_icao' => 'A320',
                'flight_time' => 3.0,
                'landing_rate' => -220,
                'route' => 'SYD W202 CS',
                'log' => 'Great tailwind enroute. ILS RWY 15.',
                'status' => 'approved',
                'submitted_at' => now()->subDay(),
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
            [
                'user_id' => 2,
                'flight_number' => 'ASR801',
                'departure' => 'YSSY',
                'arrival' => 'NZAA',
                'aircraft_registration' => 'VH-NXE',
                'aircraft_icao' => 'B789',
                'flight_time' => 3.0,
                'landing_rate' => -150,
                'route' => 'SYD H66 ARBEY G591 AKTIM',
                'log' => 'Night crossing of Tasman. STAR arrival AKTIM.',
                'status' => 'pending',
                'submitted_at' => now()->subHours(6),
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ],
        ];

        foreach ($pireps as $data) {
            Pirep::create($data);
        }
    }
}
