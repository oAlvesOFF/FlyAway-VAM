<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Tour;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            ['name' => 'First Flight', 'slug' => 'first-flight', 'description' => 'File your first PIREP', 'icon' => '🛩️', 'category' => 'flights', 'threshold' => 1, 'metric' => 'total_flights'],
            ['name' => 'Weekend Warrior', 'slug' => 'weekend-warrior', 'description' => 'Complete 10 flights', 'icon' => '✈️', 'category' => 'flights', 'threshold' => 10, 'metric' => 'total_flights'],
            ['name' => 'Century Club', 'slug' => 'century-club', 'description' => 'Complete 100 flights', 'icon' => '🏆', 'category' => 'flights', 'threshold' => 100, 'metric' => 'total_flights'],
            ['name' => 'Sky King', 'slug' => 'sky-king', 'description' => 'Complete 500 flights', 'icon' => '👑', 'category' => 'flights', 'threshold' => 500, 'metric' => 'total_flights'],
            ['name' => 'First Hours', 'slug' => 'first-hours', 'description' => 'Log 10 flight hours', 'icon' => '⏱️', 'category' => 'hours', 'threshold' => 10, 'metric' => 'total_hours'],
            ['name' => 'Century Hours', 'slug' => 'century-hours', 'description' => 'Log 100 flight hours', 'icon' => '🕐', 'category' => 'hours', 'threshold' => 100, 'metric' => 'total_hours'],
            ['name' => 'Veteran Pilot', 'slug' => 'veteran-pilot', 'description' => 'Log 1,000 flight hours', 'icon' => '⭐', 'category' => 'hours', 'threshold' => 1000, 'metric' => 'total_hours'],
            ['name' => 'Butter Landing', 'slug' => 'butter-landing', 'description' => 'Score 5 perfect landings (score 100)', 'icon' => '🦋', 'category' => 'skill', 'threshold' => 5, 'metric' => 'perfect_landings'],
            ['name' => 'Silk Touch', 'slug' => 'silk-touch', 'description' => 'Score 25 perfect landings', 'icon' => '✨', 'category' => 'skill', 'threshold' => 25, 'metric' => 'perfect_landings'],
            ['name' => 'Route Explorer', 'slug' => 'route-explorer', 'description' => 'Fly 10 different routes', 'icon' => '🗺️', 'category' => 'exploration', 'threshold' => 10, 'metric' => 'routes_flown'],
            ['name' => 'World Traveler', 'slug' => 'world-traveler', 'description' => 'Fly 25 different routes', 'icon' => '🌍', 'category' => 'exploration', 'threshold' => 25, 'metric' => 'routes_flown'],
            ['name' => 'Dedicated Flyer', 'slug' => 'dedicated-flyer', 'description' => 'File 50 PIREPs', 'icon' => '📋', 'category' => 'flights', 'threshold' => 50, 'metric' => 'pireps_filed'],
        ];

        foreach ($achievements as $data) {
            Achievement::firstOrCreate(['slug' => $data['slug']], $data);
        }

        $tours = [
            [
                'name' => 'Australian East Coast',
                'slug' => 'east-coast-australia',
                'description' => 'Fly along the beautiful Australian east coast',
                'category' => 'regional',
                'waypoints' => ['YBCS', 'YBBN', 'YSSY', 'YMML'],
                'order' => 1,
            ],
            [
                'name' => 'Trans-Tasman',
                'slug' => 'trans-tasman',
                'description' => 'Cross the Tasman Sea between Australia and New Zealand',
                'category' => 'regional',
                'waypoints' => ['YSSY', 'NZAA', 'YMML', 'NZAA'],
                'order' => 2,
            ],
            [
                'name' => 'Asia Pacific',
                'slug' => 'asia-pacific',
                'description' => 'Connect the Pacific region through major hubs',
                'category' => 'international',
                'waypoints' => ['YSSY', 'WSSS', 'RJTT', 'YPPH'],
                'order' => 3,
            ],
        ];

        foreach ($tours as $data) {
            Tour::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
