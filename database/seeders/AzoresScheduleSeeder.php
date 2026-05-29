<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Seeder;

class AzoresScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = [
            // Portugal - Azores (Airbus A21n)
            ['flight_number' => 'AZR101', 'departure' => 'LPPT', 'arrival' => 'LPPD', 'route' => 'LIS DCT LPPD', 'aircraft_type' => 'a21n', 'flight_time' => 2.5, 'departure_time' => '07:00', 'altitude' => 35000],
            ['flight_number' => 'AZR102', 'departure' => 'LPPD', 'arrival' => 'LPPT', 'route' => 'LPPD DCT LIS', 'aircraft_type' => 'a21n', 'flight_time' => 2.5, 'departure_time' => '09:00', 'altitude' => 35000],
            ['flight_number' => 'AZR201', 'departure' => 'LPPT', 'arrival' => 'LPLA', 'route' => 'LIS DCT LPLA', 'aircraft_type' => 'a21n', 'flight_time' => 2.5, 'departure_time' => '11:00', 'altitude' => 35000],
            ['flight_number' => 'AZR202', 'departure' => 'LPLA', 'arrival' => 'LPPT', 'route' => 'LPLA DCT LIS', 'aircraft_type' => 'a21n', 'flight_time' => 2.5, 'departure_time' => '13:00', 'altitude' => 35000],
            ['flight_number' => 'AZR301', 'departure' => 'LPPR', 'arrival' => 'LPPD', 'route' => 'OPO DCT LPPD', 'aircraft_type' => 'a21n', 'flight_time' => 2.5, 'departure_time' => '15:00', 'altitude' => 34000],
            ['flight_number' => 'AZR302', 'departure' => 'LPPD', 'arrival' => 'LPPR', 'route' => 'LPPD DCT OPO', 'aircraft_type' => 'a21n', 'flight_time' => 2.5, 'departure_time' => '17:00', 'altitude' => 34000],
            ['flight_number' => 'AZR401', 'departure' => 'LPPT', 'arrival' => 'LPST', 'route' => 'LIS DCT LPST', 'aircraft_type' => 'a21n', 'flight_time' => 3.0, 'departure_time' => '18:00', 'altitude' => 36000],
            ['flight_number' => 'AZR402', 'departure' => 'LPST', 'arrival' => 'LPPT', 'route' => 'LPST DCT LIS', 'aircraft_type' => 'a21n', 'flight_time' => 3.0, 'departure_time' => '20:00', 'altitude' => 36000],
        ];

        foreach ($schedules as $data) {
            Schedule::create($data);
        }
    }
}
