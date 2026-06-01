<?php

use Livewire\Volt\Component;

?>

<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pilot Handbook</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Everything you need to know about flying with Atlantic Star Airways.</p>
    </div>

    <div class="flex flex-wrap gap-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
            'getting-started' => 'Getting Started',
            'bookings' => 'Bookings & Flights',
            'pireps' => 'PIREPs & Scoring',
            'simbrief' => 'SimBrief',
            'ranks' => 'Ranks & Progression',
            'acars' => 'ACARS Tracking',
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <button wire:click="$set('section', '<?php echo e($key); ?>')"
                class="px-4 py-1.5 rounded-xl text-sm font-medium transition-all duration-150 <?php echo e($section === $key ? 'bg-crimson-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'); ?>">
                <?php echo e($label); ?>

            </button>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

    <div class="card p-6 prose prose-sm dark:prose-invert max-w-none">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($section):
            case ('getting-started'): ?>
                <h2>Getting Started</h2>
                <p>Welcome to Atlantic Star Airways! Follow these steps to begin your virtual aviation career:</p>
                <ol>
                    <li><strong>Create an Account</strong> — Register on the website using your email and preferred callsign.</li>
                    <li><strong>Set Up SimBrief</strong> — Go to your Profile and enter your SimBrief username to enable automatic flight planning.</li>
                    <li><strong>Browse Flights</strong> — Visit the Flights page to see all available schedules. Use the filters to narrow by departure, arrival, or aircraft type.</li>
                    <li><strong>Book a Flight</strong> — Select an available flight and book it. You'll see it in My Bookings.</li>
                    <li><strong>Fly Your Route</strong> — Use your favorite flight simulator (MSFS, X-Plane, P3D) to fly the route.</li>
                    <li><strong>File a PIREP</strong> — After landing, file a Pilot Report with your flight time and landing rate.</li>
                    <li><strong>Get Approved</strong> — An admin will review and approve your PIREP. Your hours and rank will update automatically.</li>
                </ol>
                <p>That's it! Each flight builds your hours and progresses your career.</p>
            <?php break; ?>

            <?php case ('bookings'): ?>
                <h2>Bookings &amp; Flight Operations</h2>
                <h3>Booking a Flight</h3>
                <p>The Flights page lists all active schedules. You can filter by departure airport, arrival airport, and aircraft type. Each flight shows the flight number, route, aircraft type, and flight duration.</p>
                <p>To book, click "Book Flight" on any available schedule. The system checks rank requirements — you can only book aircraft types your rank allows.</p>
                <h3>Managing Bookings</h3>
                <p>My Bookings shows all your upcoming flights. You can cancel any booking from this page. After filing a PIREP for a booking, it is automatically removed.</p>
                <h3>Aircraft Categories</h3>
                <ul>
                    <li><strong>B737</strong> — Boeing 737-800/737 MAX (regional/short-haul)</li>
                    <li><strong>A320</strong> — Airbus A320 family (medium-haul)</li>
                    <li><strong>B787</strong> — Boeing 787 Dreamliner (long-haul)</li>
                    <li><strong>B777</strong> — Boeing 777 (long/ultra-long-haul)</li>
                    <li><strong>A380</strong> — Airbus A380 (ultra-long-haul)</li>
                </ul>
            <?php break; ?>

            <?php case ('pireps'): ?>
                <h2>PIREPs &amp; Scoring</h2>
                <h3>What is a PIREP?</h3>
                <p>A Pilot Report (PIREP) is how you log your completed flights. Each PIREP includes the flight number, route, aircraft, flight time, landing rate, and an optional route string and log.</p>
                <h3>How Scoring Works</h3>
                <p>Your PIREP score is calculated from your landing rate (in feet per minute):</p>
                <ul>
                    <li><strong>100 points</strong> — Landing rate between -500 and +100 fpm (smooth landing)</li>
                    <li><strong>80 points</strong> — Landing rate between -300 and +50 fpm (good landing, tighter range)</li>
                    <li><strong>60 points</strong> — Landing rate below -500 or above +100 fpm (firm landing)</li>
                </ul>
                <h3>Approval Process</h3>
                <p>After filing, your PIREP enters "Pending" status. An admin reviews and either approves or rejects it. Upon approval:</p>
                <ul>
                    <li>Your total hours increase by the flight time</li>
                    <li>Your total flights increment</li>
                    <li>Your home location updates to the arrival airport</li>
                    <li>Your rank may increase if you meet the next rank's hour requirement</li>
                    <li>The aircraft's location updates to the arrival airport</li>
                </ul>
            <?php break; ?>

            <?php case ('simbrief'): ?>
                <h2>SimBrief Integration</h2>
                <p>Atlantic Star Airways integrates with SimBrief to provide detailed Operational Flight Plans (OFPs).</p>
                <h3>Setup</h3>
                <ol>
                    <li>Create a SimBrief account at <a href="https://www.simbrief.com" target="_blank">simbrief.com</a></li>
                    <li>Note your SimBrief username (found in your profile settings)</li>
                    <li>Go to your Pilot Profile on our site and enter your SimBrief username</li>
                </ol>
                <h3>Generating a Briefing</h3>
                <p>Go to the SimBrief page, select one of your bookings, and click "Fetch Briefing". The system generates an OFP and displays it across five tabs:</p>
                <ul>
                    <li><strong>Route</strong> — Waypoints, airways, and route string</li>
                    <li><strong>Weather</strong> — Departure and destination weather brief</li>
                    <li><strong>Fuel</strong> — Fuel planning data including trip fuel, reserves, and alternate fuel</li>
                    <li><strong>NavLog</strong> — Detailed navigation log with waypoints, distances, and ETAs</li>
                    <li><strong>Files</strong> — Download links for PDF briefing and XML import files</li>
                </ul>
            <?php break; ?>

            <?php case ('ranks'): ?>
                <h2>Ranks &amp; Progression</h2>
                <p>Progress through five ranks by accumulating flight hours. Each rank unlocks new aircraft categories.</p>
                <div class="overflow-x-auto not-prose my-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left border-b border-slate-200 dark:border-slate-700">
                                <th class="pb-2 font-medium">Rank</th>
                                <th class="pb-2 font-medium">Min Hours</th>
                                <th class="pb-2 font-medium">Unlocked Types</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-slate-100 dark:border-slate-800"><td class="py-2">Cadet</td><td>0</td><td>B737, A320</td></tr>
                            <tr class="border-b border-slate-100 dark:border-slate-800"><td class="py-2">First Officer</td><td>50</td><td>B737, A320</td></tr>
                            <tr class="border-b border-slate-100 dark:border-slate-800"><td class="py-2">Captain</td><td>200</td><td>B787 (new)</td></tr>
                            <tr class="border-b border-slate-100 dark:border-slate-800"><td class="py-2">Senior Captain</td><td>500</td><td>B777 (new)</td></tr>
                            <tr><td class="py-2">Fleet Captain</td><td>1000</td><td>A380 (new)</td></tr>
                        </tbody>
                    </table>
                </div>
                <p>Your Dashboard shows your current rank, progression bar toward the next rank, and which aircraft types you'll unlock next.</p>
            <?php break; ?>

            <?php case ('acars'): ?>
                <h2>ACARS Real-Time Tracking</h2>
                <p>Atlantic Star Airways supports real-time flight tracking via ACARS (Aircraft Communications Addressing and Reporting System).</p>
                <h3>How It Works</h3>
                <p>When you fly a route, your position data can be sent to our tracking server. This data appears on the Live Map for everyone to see.</p>
                <h3>Using the ACARS Client</h3>
                <p>You can use one of these methods:</p>
                <ol>
                    <li><strong>Web ACARS Client</strong> — Use our browser-based ACARS simulator on the ACARS page. Enter your flight details and start the simulation.</li>
                    <li><strong>External ACARS</strong> — Third-party tools like vPilot, xPilot, or SimToolkitPro can send position reports to our API endpoint.</li>
                </ol>
                <h3>API Endpoints</h3>
                <p>For developers and external tool integration:</p>
                <ul>
                    <li><code>GET /api/flights/active</code> — List all active tracked flights (public)</li>
                    <li><code>POST /api/flights/track</code> — Send position update (requires API token)</li>
                    <li><code>POST /api/flights/{id}/complete</code> — Mark flight as completed (requires API token)</li>
                </ul>
                <p>Contact management for API access credentials.</p>
                <h3>Live Map Features</h3>
                <ul>
                    <li>Animated aircraft icons rotated by heading</li>
                    <li>Color-coded by flight phase (preflight/boarding/departed/enroute/onapproach/landed)</li>
                    <li>Popup tooltips showing altitude, speed, heading, and phase</li>
                    <li>Auto-refresh every 10 seconds</li>
                    <li>Layer controls to toggle airports, static aircraft, routes, and active flights</li>
                </ul>
            <?php break; ?>
        <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\Users\noxxr\Desktop\FlyAway-VAM\resources\views\livewire/handbook.blade.php ENDPATH**/ ?>