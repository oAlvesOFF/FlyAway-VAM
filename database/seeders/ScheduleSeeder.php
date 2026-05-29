<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = [
            // Domestic Australia - B737
            ['flight_number' => 'ASR101', 'departure' => 'YSSY', 'arrival' => 'YMML', 'route' => 'SYD RIC H66 ML', 'aircraft_type' => 'B737', 'flight_time' => 1.5, 'departure_time' => '06:00', 'altitude' => 34000],
            ['flight_number' => 'ASR102', 'departure' => 'YMML', 'arrival' => 'YSSY', 'route' => 'ML H66 RIC SYD', 'aircraft_type' => 'B737', 'flight_time' => 1.5, 'departure_time' => '08:00', 'altitude' => 34000],
            ['flight_number' => 'ASR201', 'departure' => 'YSSY', 'arrival' => 'YBBN', 'route' => 'SYD W340 BN', 'aircraft_type' => 'B737', 'flight_time' => 1.0, 'departure_time' => '09:00', 'altitude' => 28000],
            ['flight_number' => 'ASR301', 'departure' => 'YSSY', 'arrival' => 'YPPH', 'route' => 'SYD J17 AD J123 PH', 'aircraft_type' => 'B737', 'flight_time' => 4.0, 'departure_time' => '07:00', 'altitude' => 36000],
            // Domestic Australia - A320
            ['flight_number' => 'ASR401', 'departure' => 'YSSY', 'arrival' => 'YBCS', 'route' => 'SYD W202 CS', 'aircraft_type' => 'A320', 'flight_time' => 3.0, 'departure_time' => '10:00', 'altitude' => 35000],
            ['flight_number' => 'ASR402', 'departure' => 'YBCS', 'arrival' => 'YSSY', 'route' => 'CS W202 SYD', 'aircraft_type' => 'A320', 'flight_time' => 3.0, 'departure_time' => '14:00', 'altitude' => 35000],
            ['flight_number' => 'ASR403', 'departure' => 'YMML', 'arrival' => 'YPPH', 'route' => 'ML J19 PH', 'aircraft_type' => 'A320', 'flight_time' => 3.5, 'departure_time' => '11:00', 'altitude' => 36000],
            // International - B787
            ['flight_number' => 'ASR801', 'departure' => 'YSSY', 'arrival' => 'NZAA', 'route' => 'SYD H66 ARBEY G591 AKTIM', 'aircraft_type' => 'B787', 'flight_time' => 3.0, 'departure_time' => '08:00', 'altitude' => 38000],
            ['flight_number' => 'ASR802', 'departure' => 'NZAA', 'arrival' => 'YSSY', 'route' => 'AKTIM G591 ARBEY H66 SYD', 'aircraft_type' => 'B787', 'flight_time' => 3.0, 'departure_time' => '13:00', 'altitude' => 38000],
            ['flight_number' => 'ASR811', 'departure' => 'YSSY', 'arrival' => 'WSSS', 'route' => 'SYD M641 AKATO A576 BANDA N890 TPG', 'aircraft_type' => 'B787', 'flight_time' => 8.0, 'departure_time' => '10:00', 'altitude' => 40000],
            ['flight_number' => 'ASR812', 'departure' => 'WSSS', 'arrival' => 'YSSY', 'route' => 'TPG N890 BANDA A576 AKATO M641 SYD', 'aircraft_type' => 'B787', 'flight_time' => 8.0, 'departure_time' => '22:00', 'altitude' => 40000],
            // International - B777
            ['flight_number' => 'ASR901', 'departure' => 'YSSY', 'arrival' => 'RJTT', 'route' => 'SYD A579 AKNIS B586 KUBAK Y202 TTE', 'aircraft_type' => 'B777', 'flight_time' => 9.5, 'departure_time' => '09:00', 'altitude' => 40000],
            ['flight_number' => 'ASR902', 'departure' => 'RJTT', 'arrival' => 'YSSY', 'route' => 'TTE Y202 KUBAK B586 AKNIS A579 SYD', 'aircraft_type' => 'B777', 'flight_time' => 9.5, 'departure_time' => '20:00', 'altitude' => 40000],
            // International - A380
            ['flight_number' => 'ASR951', 'departure' => 'YSSY', 'arrival' => 'EGLL', 'route' => 'SYD DCT OZY DCT BIKRO DCT LL', 'aircraft_type' => 'A380', 'flight_time' => 22.0, 'departure_time' => '16:00', 'altitude' => 41000],
            ['flight_number' => 'ASR952', 'departure' => 'EGLL', 'arrival' => 'YSSY', 'route' => 'LL DCT BIKRO DCT OZY DCT SYD', 'aircraft_type' => 'A380', 'flight_time' => 22.0, 'departure_time' => '20:00', 'altitude' => 41000],
        ];

        foreach ($schedules as $data) {
            Schedule::create($data);
        }
    }
}
