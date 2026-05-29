<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $news = [
            [
                'title' => 'Welcome to Atlantic Star Airways',
                'slug' => 'welcome-to-atlantic-star-airways',
                'excerpt' => 'We are thrilled to announce the launch of Atlantic Star Airways, a new premium virtual airline experience.',
                'content' => "Welcome aboard, pilots!\n\nAtlantic Star Airways is proud to launch our operations across the Asia-Pacific region. Our fleet of Boeing and Airbus aircraft will serve 9 destinations across Australia, New Zealand, Singapore, Japan, and the United Kingdom.\n\n**What to expect:**\n- Realistic flight scheduling and booking\n- PIREP-based progression system with 5 ranks to unlock\n- SimBrief integration for detailed flight planning\n- Live ACARS tracking on our operations map\n- A growing community of virtual pilots\n\nFly the line, build your hours, and climb the ranks. The sky is yours.\n\n— Atlantic Star Management",
                'published_at' => now(),
            ],
            [
                'title' => 'New Routes: Sydney to London Now Available',
                'slug' => 'new-routes-sydeny-london',
                'excerpt' => 'Atlantic Star now offers direct service from Sydney to London Heathrow on our flagship Boeing 787-9 Dreamliner.',
                'content' => "We're excited to announce our newest long-haul route: **YSSY → EGLL** (Sydney to London Heathrow).\n\nThis route covers over 10,500 miles and is operated by our flagship Boeing 787-9 Dreamliner, registration ASR-B789.\n\n**Route Details:**\n- Flight Number: ASR800\n- Departure: Sydney (YSSY) — 10:00 UTC\n- Arrival: London Heathrow (EGLL) — 18:30 UTC\n- Flight Time: 14.5 hours\n- Aircraft: Boeing 787-9 Dreamliner\n\nBook your flight today from the Flights page and experience long-haul flying at its finest.",
                'published_at' => now(),
            ],
            [
                'title' => 'Rank Progression System Live',
                'slug' => 'rank-progression-system-live',
                'excerpt' => 'Fly, earn hours, and unlock new aircraft categories with our new rank progression system.',
                'content' => "Our rank progression system is now live! Here's how it works:\n\n**The Ranks:**\n1. **Cadet** (0 hrs) — B737, A320\n2. **First Officer** (50 hrs) — B737, A320\n3. **Captain** (200 hrs) — B787\n4. **Senior Captain** (500 hrs) — B777\n5. **Fleet Captain** (1000 hrs) — A380\n\n**How to Progress:**\n1. File PIREPs for your flights\n2. Wait for admin approval\n3. Your hours and flights automatically update\n4. Once you meet the minimum hours, you're promoted!\n\nEach rank unlocks access to new aircraft types. Work your way up to command the A380!",
                'published_at' => now()->subDay(),
            ],
        ];

        foreach ($news as $item) {
            $item['author_id'] = 1;
            News::create($item);
        }
    }
}
