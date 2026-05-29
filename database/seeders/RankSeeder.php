<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    public function run(): void
    {
        $ranks = [
            ['name' => 'Second Officer', 'minimum_hours' => 0, 'allowed_categories' => 'B737,A320,CRJ'],
            ['name' => 'First Officer', 'minimum_hours' => 100, 'allowed_categories' => 'B737,A320,CRJ,B787'],
            ['name' => 'Captain', 'minimum_hours' => 500, 'allowed_categories' => 'B737,A320,B787,B777'],
            ['name' => 'Senior Captain', 'minimum_hours' => 1500, 'allowed_categories' => 'B737,A320,B787,B777,A380'],
            ['name' => 'Chief Pilot', 'minimum_hours' => 5000, 'allowed_categories' => 'B737,A320,B787,B777,A380'],
        ];

        foreach ($ranks as $rank) {
            Rank::create($rank);
        }
    }
}
