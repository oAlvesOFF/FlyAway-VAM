<?php

namespace Database\Seeders;

use App\Models\Bid;
use Illuminate\Database\Seeder;

class BidSeeder extends Seeder
{
    public function run(): void
    {
        Bid::create([
            'user_id' => 2,
            'schedule_id' => 4,
            'aircraft_id' => 2,
        ]);

        Bid::create([
            'user_id' => 2,
            'schedule_id' => 9,
            'aircraft_id' => 5,
        ]);
    }
}
