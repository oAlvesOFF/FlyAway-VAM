<?php

namespace Database\Seeders;

use App\Models\Aircraft;
use Illuminate\Database\Seeder;

class AircraftSeeder extends Seeder
{
    public function run(): void
    {
        $aircraft = [
            ['registration' => 'VH-NXC', 'icao' => 'B738', 'name' => 'Boeing 737-800', 'location' => 'YSSY', 'status' => 'active', 'category' => 'B737'],
            ['registration' => 'VH-NXD', 'icao' => 'B738', 'name' => 'Boeing 737-800', 'location' => 'YMML', 'status' => 'active', 'category' => 'B737'],
            ['registration' => 'VH-NXA', 'icao' => 'A320', 'name' => 'Airbus A320-200', 'location' => 'YSSY', 'status' => 'active', 'category' => 'A320'],
            ['registration' => 'VH-NXB', 'icao' => 'A320', 'name' => 'Airbus A320-200', 'location' => 'YBBN', 'status' => 'active', 'category' => 'A320'],
            ['registration' => 'VH-NXE', 'icao' => 'B789', 'name' => 'Boeing 787-9', 'location' => 'YSSY', 'status' => 'active', 'category' => 'B787'],
            ['registration' => 'VH-NXF', 'icao' => 'B789', 'name' => 'Boeing 787-9', 'location' => 'YPPH', 'status' => 'active', 'category' => 'B787'],
            ['registration' => 'VH-NXG', 'icao' => 'B772', 'name' => 'Boeing 777-200ER', 'location' => 'YSSY', 'status' => 'active', 'category' => 'B777'],
            ['registration' => 'VH-NXH', 'icao' => 'A388', 'name' => 'Airbus A380-800', 'location' => 'YSSY', 'status' => 'active', 'category' => 'A380'],
        ];

        foreach ($aircraft as $data) {
            Aircraft::create($data);
        }
    }
}
