<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FlyAway Virtual | The Virtual Airline Built for Pilots Who Want More</title>
    <meta name="description" content="{{ number_format($scheduleCount) }}+ real-world routes. Rank progression, ACARS tracking, and a platform unlike anything else in virtual aviation.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { box-sizing: border-box; }
        html { font-family: 'Inter', sans-serif; }
        body {
            background-color: #0a0a0a;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        /* ──────────────── NAVBAR ──────────────── */
        .nav-link {
            font-size: 14px;
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            transition: color 0.2s;
        }
        .nav-link:hover { color: #fff; }

        /* ──────────────── BUTTONS ──────────────── */
        .btn-red {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #dc2626;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            padding: 11px 22px;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.2s, transform 0.15s;
            border: none;
            cursor: pointer;
        }
        .btn-red:hover { background: #b91c1c; transform: translateY(-1px); }

        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            color: rgba(255,255,255,0.85);
            font-weight: 600;
            font-size: 14px;
            padding: 11px 22px;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.2);
            transition: border-color 0.2s, color 0.2s, transform 0.15s;
            cursor: pointer;
        }
        .btn-outline:hover { border-color: rgba(255,255,255,0.5); color: #fff; transform: translateY(-1px); }

        /* ──────────────── HERO ──────────────── */
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 13px;
            color: rgba(255,255,255,0.8);
            margin-bottom: 32px;
        }
        .hero-badge .dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 6px #22c55e;
            animation: pulse-green 2s infinite;
        }
        @keyframes pulse-green {
            0%,100% { opacity:1; box-shadow: 0 0 6px #22c55e; }
            50% { opacity:0.6; box-shadow: 0 0 14px #22c55e; }
        }

        .hero-title {
            font-size: clamp(36px, 6vw, 68px);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -1.5px;
            color: #fff;
            margin-bottom: 20px;
        }
        .hero-title .accent { color: #dc2626; }

        .hero-sub {
            font-size: 16px;
            color: rgba(255,255,255,0.5);
            max-width: 520px;
            margin: 0 auto 36px;
            line-height: 1.65;
        }

        /* ──────────────── BROWSER MOCKUP ──────────────── */
        .browser-frame {
            background: #161616;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 40px 120px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.05);
        }
        .browser-topbar {
            background: #1a1a1a;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .browser-dots { display: flex; gap: 6px; }
        .browser-dot {
            width: 10px; height: 10px; border-radius: 50%;
        }
        .browser-dot.red { background: #ff5f57; }
        .browser-dot.yellow { background: #febc2e; }
        .browser-dot.green { background: #28c840; }
        .browser-url {
            flex: 1;
            background: rgba(255,255,255,0.05);
            border-radius: 6px;
            padding: 4px 12px;
            font-size: 11px;
            color: rgba(255,255,255,0.4);
            font-family: 'JetBrains Mono', monospace;
            text-align: center;
            margin: 0 12px;
        }

        /* ──────────────── SECTION LABELS ──────────────── */
        .section-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #dc2626;
            margin-bottom: 14px;
            display: block;
        }
        .section-title {
            font-size: clamp(28px, 4vw, 44px);
            font-weight: 800;
            letter-spacing: -1px;
            color: #fff;
            line-height: 1.2;
        }
        .section-sub {
            font-size: 15px;
            color: rgba(255,255,255,0.45);
            line-height: 1.7;
            max-width: 560px;
            margin: 16px auto 0;
        }

        /* ──────────────── DARK CARD ──────────────── */
        .dark-card {
            background: #111111;
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 14px;
            overflow: hidden;
            transition: border-color 0.25s;
        }
        .dark-card:hover { border-color: rgba(255,255,255,0.14); }
        .dark-card .card-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.3);
            margin-bottom: 8px;
        }

        /* ──────────────── DOT GRID ──────────────── */
        .dot-grid {
            background-image: radial-gradient(circle, rgba(255,255,255,0.12) 1px, transparent 1px);
            background-size: 24px 24px;
        }

        /* ──────────────── FLIGHT TRACKER CARD ──────────────── */
        .flight-card {
            background: #1a1a1a;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 16px 20px;
        }
        .flight-card .phase-badge {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 4px;
        }
        .phase-cruise   { background: rgba(34,197,94,0.12);  color: #22c55e; }
        .phase-descent  { background: rgba(234,179,8,0.12);  color: #eab308; }
        .phase-boarding { background: rgba(59,130,246,0.12); color: #3b82f6; }
        .phase-enroute  { background: rgba(168,85,247,0.12); color: #a855f7; }
        .phase-default  { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.5); }

        .route-line {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 12px 0 10px;
        }
        .route-icao { font-size: 18px; font-weight: 800; letter-spacing: -0.5px; color: #fff; }
        .route-arrow {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .route-dash {
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, rgba(220,38,38,0.6), rgba(220,38,38,0.2));
        }
        .route-plane { color: #dc2626; font-size: 13px; }
        .flight-meta { display: flex; gap: 16px; }
        .meta-item { font-size: 11px; }
        .meta-item .key { color: rgba(255,255,255,0.3); display: block; font-weight: 500; letter-spacing: 0.5px; text-transform: uppercase; font-size: 9px; margin-bottom: 2px; }
        .meta-item .val { color: #fff; font-weight: 600; font-family: 'JetBrains Mono', monospace; }

        /* ──────────────── ACTIVITY LOG ──────────────── */
        .activity-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            font-size: 12px;
        }
        .activity-row:last-child { border-bottom: none; }
        .activity-icon { width: 20px; text-align: center; font-size: 12px; }
        .activity-text { flex: 1; color: rgba(255,255,255,0.7); }
        .activity-text strong { color: #fff; }
        .activity-time { color: rgba(255,255,255,0.3); font-family: 'JetBrains Mono', monospace; font-size: 11px; }

        /* ──────────────── STAT CELLS ──────────────── */
        .stat-cell {
            background: #111;
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 12px;
            padding: 24px 20px;
            text-align: center;
            transition: border-color 0.25s;
        }
        .stat-cell:hover { border-color: rgba(220,38,38,0.3); }
        .stat-icon { font-size: 22px; margin-bottom: 12px; color: #dc2626; }
        .stat-label { font-size: 11px; color: rgba(255,255,255,0.35); letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 4px; }
        .stat-num { font-size: 26px; font-weight: 800; color: #fff; letter-spacing: -1px; }

        /* ──────────────── TESTIMONIALS ──────────────── */
        .testimonial-card {
            background: #111;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: 28px 24px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .stars { color: #dc2626; font-size: 14px; letter-spacing: 2px; }
        .testi-text {
            font-size: 14px;
            color: rgba(255,255,255,0.65);
            line-height: 1.7;
            flex: 1;
        }
        .testi-author { display: flex; align-items: center; gap: 12px; }
        .testi-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg, #dc2626, #7f1d1d);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px; color: #fff; flex-shrink: 0;
        }
        .testi-name { font-size: 14px; font-weight: 600; color: #fff; }
        .testi-id   { font-size: 12px; color: rgba(255,255,255,0.3); font-family: 'JetBrains Mono', monospace; }

        /* ──────────────── LIVE MAP ──────────────── */
        .leaflet-popup-content-wrapper {
            background: #111 !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            color: #fff !important;
            border-radius: 10px !important;
            box-shadow: 0 8px 32px rgba(0,0,0,0.6) !important;
        }
        .leaflet-popup-tip { background: #111 !important; }
        .leaflet-container { background: #0a0a0a !important; }

        /* ──────────────── FOOTER ──────────────── */
        .footer-link { font-size: 13px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s; }
        .footer-link:hover { color: rgba(255,255,255,0.7); }

        /* ──────────────── UTILITIES ──────────────── */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
        .red { color: #dc2626; }
        .divider { border: none; border-top: 1px solid rgba(255,255,255,0.07); margin: 0; }
    </style>
</head>
<body>

{{-- ─────────────────────────────────────────── --}}
{{-- NAVBAR                                       --}}
{{-- ─────────────────────────────────────────── --}}
<nav style="position:fixed;top:0;left:0;right:0;z-index:100;background:rgba(10,10,10,0.85);backdrop-filter:blur(20px);border-bottom:1px solid rgba(255,255,255,0.07);">
    <div class="container" style="display:flex;align-items:center;height:60px;gap:32px;">
        {{-- Logo --}}
        <a href="/" style="display:flex;align-items:center;gap:10px;text-decoration:none;flex-shrink:0;">
            <div style="width:32px;height:32px;background:#dc2626;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </div>
            <span style="font-size:17px;font-weight:800;color:#fff;letter-spacing:-0.5px;">FLY<span style="color:#dc2626;">AWAY</span></span>
        </a>

        {{-- Nav Links --}}
        <div style="display:flex;align-items:center;gap:28px;flex:1;">
            <a href="/" class="nav-link">Home</a>
            <a href="#features" class="nav-link">About</a>
            <a href="#live-map" class="nav-link">Live Flights</a>
            <a href="#schedules" class="nav-link">Flight Crew</a>
            <a href="#stats" class="nav-link">Statistics</a>
        </div>

        {{-- Right actions --}}
        <div style="display:flex;align-items:center;gap:12px;">
            @auth
                <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
                <a href="{{ route('dashboard') }}" class="btn-red">Go to Cockpit →</a>
            @else
                <a href="{{ route('login') }}" class="nav-link">Login</a>
                <a href="{{ route('register') }}" class="btn-red">Sign up</a>
            @endauth
        </div>
    </div>
</nav>


{{-- ─────────────────────────────────────────── --}}
{{-- HERO                                         --}}
{{-- ─────────────────────────────────────────── --}}
<section style="padding:140px 0 80px;text-align:center;position:relative;">
    <div class="container">

        {{-- Live badge --}}
        <div style="display:flex;justify-content:center;margin-bottom:36px;">
            <span class="hero-badge">
                <span class="dot"></span>
                ✈ {{ count($mappedFlights) }} live flights ›
            </span>
        </div>

        {{-- Title --}}
        <h1 class="hero-title">
            The Virtual Airline built<br>
            for pilots who <span class="accent">want more</span>
        </h1>

        {{-- Sub --}}
        <p class="hero-sub">
            {{ number_format($scheduleCount) }}+ real-world routes. Rank progression, ACARS tracking,
            daily challenges, and a platform unlike anything else in virtual aviation.
        </p>

        {{-- CTAs --}}
        <div style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap;margin-bottom:72px;">
            @auth
                <a href="{{ route('flights') }}" class="btn-red">Find a Flight →</a>
                <a href="{{ route('dashboard') }}" class="btn-outline">Go to Dashboard</a>
            @else
                <a href="{{ route('register') }}" class="btn-red">Create account →</a>
                <a href="#features" class="btn-outline">Explore features</a>
            @endauth
        </div>

        {{-- Browser Mockup with Dashboard Preview --}}
        <div class="browser-frame" style="max-width:900px;margin:0 auto;position:relative;">
            <div class="browser-topbar">
                <div class="browser-dots">
                    <div class="browser-dot red"></div>
                    <div class="browser-dot yellow"></div>
                    <div class="browser-dot green"></div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;margin-left:8px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                </div>
                <div class="browser-url">flyaway.virtual</div>
                <div style="display:flex;gap:8px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.25)" stroke-width="2"><path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8"/><polyline points="16,6 12,2 8,6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                </div>
            </div>

            {{-- Dashboard UI Mockup --}}
            <div style="background:#0d0d0d;display:flex;height:360px;overflow:hidden;">
                {{-- Sidebar --}}
                <div style="width:180px;background:#111;border-right:1px solid rgba(255,255,255,0.07);padding:16px 0;flex-shrink:0;">
                    <div style="padding:0 16px 12px;border-bottom:1px solid rgba(255,255,255,0.06);margin-bottom:10px;">
                        <span style="font-size:13px;font-weight:800;color:#fff;">FLY<span style="color:#dc2626;">AWAY</span></span>
                    </div>
                    <div style="padding:0 10px;font-size:10px;color:rgba(255,255,255,0.3);font-weight:600;letter-spacing:1px;text-transform:uppercase;margin-bottom:6px;padding-left:16px;">Navigation</div>
                    @php $navItems = ['Dashboard','Bookings','Schedules','Live map','Explore','Pilot Services','Statistics']; @endphp
                    @foreach($navItems as $i => $item)
                    <div style="display:flex;align-items:center;gap:10px;padding:8px 16px;{{ $i===0 ? 'background:rgba(220,38,38,0.12);border-left:2px solid #dc2626;' : '' }}font-size:12px;color:{{ $i===0 ? '#fff' : 'rgba(255,255,255,0.4)' }};cursor:pointer;">
                        <div style="width:14px;height:14px;background:rgba(255,255,255,0.08);border-radius:3px;"></div>
                        {{ $item }}
                        @if($i===1)<span style="margin-left:auto;font-size:10px;background:#dc2626;color:#fff;border-radius:4px;padding:1px 5px;">5</span>@endif
                    </div>
                    @endforeach
                </div>
                {{-- Main area --}}
                <div style="flex:1;padding:20px;overflow:hidden;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <div>
                            <div style="font-size:16px;font-weight:700;color:#fff;">Welcome back, Pilot 👋</div>
                            <div style="font-size:12px;color:rgba(255,255,255,0.35);">Where will you be flying today?</div>
                        </div>
                        <button class="btn-red" style="font-size:12px;padding:8px 16px;">FIND A FLIGHT</button>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div style="background:#1a1a1a;border:1px solid rgba(255,255,255,0.07);border-radius:10px;overflow:hidden;height:200px;">
                            <img src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=400&q=80" style="width:100%;height:100%;object-fit:cover;opacity:0.7;" alt="Aircraft">
                        </div>
                        <div style="background:#1a1a1a;border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:16px;">
                            <div style="font-size:10px;color:rgba(255,255,255,0.3);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Your Profile</div>
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                                <div style="width:32px;height:32px;background:linear-gradient(135deg,#dc2626,#7f1d1d);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;">P</div>
                                <div>
                                    <div style="font-size:13px;font-weight:600;color:#fff;">{{ auth()->user()?->name ?? 'Guest Pilot' }}</div>
                                    <div style="font-size:10px;color:rgba(255,255,255,0.3);font-family:'JetBrains Mono',monospace;">{{ auth()->user()?->pilot_id ?? 'FVA0001' }}</div>
                                </div>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                                <div style="background:rgba(255,255,255,0.04);border-radius:6px;padding:10px;">
                                    <div style="font-size:9px;color:rgba(255,255,255,0.3);text-transform:uppercase;letter-spacing:1px;">Pilots Online</div>
                                    <div style="font-size:20px;font-weight:800;color:#fff;">{{ number_format($pilotCount) }}</div>
                                </div>
                                <div style="background:rgba(255,255,255,0.04);border-radius:6px;padding:10px;">
                                    <div style="font-size:9px;color:rgba(255,255,255,0.3);text-transform:uppercase;letter-spacing:1px;">Live Flights</div>
                                    <div style="font-size:20px;font-weight:800;color:#fff;">{{ count($mappedFlights) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Red glow behind hero --}}
    <div style="position:absolute;bottom:-100px;left:50%;transform:translateX(-50%);width:600px;height:300px;background:radial-gradient(ellipse,rgba(220,38,38,0.08),transparent 70%);pointer-events:none;"></div>
</section>


{{-- ─────────────────────────────────────────── --}}
{{-- FEATURES                                     --}}
{{-- ─────────────────────────────────────────── --}}
<section id="features" style="padding:100px 0;">
    <div class="container">
        <div style="text-align:center;margin-bottom:64px;">
            <span class="section-label">Features</span>
            <h2 class="section-title">What Makes Us Different</h2>
            <p class="section-sub">FlyAway sets the standard for virtual aviation. Cutting-edge features, real airline data, and a platform built for pilots who take it seriously.</p>
        </div>

        {{-- Feature Grid --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

            {{-- Flight Network Card (large) --}}
            <div class="dark-card" style="padding:36px;position:relative;min-height:320px;">
                <div class="card-label">Flight Network</div>
                <h3 style="font-size:30px;font-weight:800;color:#fff;line-height:1.2;margin-bottom:16px;letter-spacing:-0.5px;">
                    {{ number_format($scheduleCount) }}+ schedules.<br>Worldwide coverage.
                </h3>
                <p style="font-size:14px;color:rgba(255,255,255,0.4);line-height:1.65;margin-bottom:20px;max-width:340px;">
                    Real-world routes spanning every continent. Long-haul widebodies, regional turboprops, cargo ops — if it flies, we've got it.
                </p>
                <a href="#schedules" style="font-size:13px;font-weight:600;color:#dc2626;text-decoration:none;">{{ number_format($scheduleCount) }}+ active schedules →</a>

                {{-- Dot grid map background --}}
                <div class="dot-grid" style="position:absolute;right:0;top:0;bottom:0;width:55%;border-radius:0 14px 14px 0;opacity:0.4;"></div>

                {{-- Simulated route dots --}}
                @php $dots = [[75,35],[55,25],[60,55],[80,60],[45,70],[70,75]]; @endphp
                @foreach($dots as $d)
                <div style="position:absolute;right:{{ $d[0] }}px;top:{{ $d[1] }}%;width:8px;height:8px;background:#dc2626;border-radius:50%;box-shadow:0 0 8px rgba(220,38,38,0.6);"></div>
                @endforeach
            </div>

            {{-- Right column (2 cards) --}}
            <div style="display:grid;grid-template-rows:1fr 1fr;gap:16px;">

                {{-- Challenges --}}
                <div class="dark-card" style="padding:28px;position:relative;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                        <div class="card-label" style="margin-bottom:0;">Challenges</div>
                        <span style="background:#dc2626;color:#fff;font-size:9px;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:2px 7px;border-radius:4px;">NEW</span>
                    </div>
                    <h3 style="font-size:22px;font-weight:800;color:#fff;margin-bottom:10px;letter-spacing:-0.3px;">Daily & Weekly</h3>
                    <p style="font-size:13px;color:rgba(255,255,255,0.4);line-height:1.6;margin-bottom:16px;">Fresh challenges every day. Earn rewards, compete with other pilots, and push your skills.</p>

                    {{-- Mini challenges --}}
                    @php
                    $challenges = [
                        ['Land at YSSY','Land at Kingsford Smith Intl','DAILY','#f59e0b',100,100,'21SC + $500','Completed'],
                        ['Fly to New Zealand','Land at any NZ airport','DAILY','#f59e0b',80,100,'21SC + $500','8/1'],
                        ['Regional airports','Land at 2 regional airports','WEEKLY','#3b82f6',33,100,'100SC + $2,000','1/2'],
                    ];
                    @endphp
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @foreach($challenges as $c)
                        <div style="background:#1a1a1a;border:1px solid rgba(255,255,255,0.06);border-radius:8px;padding:10px 12px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                                <div style="font-size:12px;font-weight:600;color:#fff;">{{ $c[0] }}</div>
                                <span style="font-size:9px;font-weight:700;letter-spacing:1px;background:rgba({{ $c[3]==='#f59e0b'?'245,158,11':'59,130,246' }},0.12);color:{{ $c[3] }};padding:2px 7px;border-radius:4px;text-transform:uppercase;">{{ $c[2] }}</span>
                            </div>
                            <div style="font-size:10px;color:rgba(255,255,255,0.3);margin-bottom:6px;">{{ $c[1] }}</div>
                            <div style="background:rgba(255,255,255,0.06);border-radius:4px;height:4px;overflow:hidden;">
                                <div style="width:{{ $c[4] }}%;height:100%;background:#22c55e;border-radius:4px;"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Immersive Tours --}}
                <div class="dark-card" style="padding:28px;">
                    <div class="card-label">Explore</div>
                    <h3 style="font-size:22px;font-weight:800;color:#fff;margin-bottom:10px;letter-spacing:-0.3px;">Immersive Tours</h3>
                    <p style="font-size:13px;color:rgba(255,255,255,0.4);line-height:1.6;margin-bottom:16px;">Curated multi-leg journeys across the globe. Track your progress checkpoint by checkpoint.</p>
                    <div style="background:#1a1a1a;border:1px solid rgba(255,255,255,0.07);border-radius:8px;padding:14px;">
                        <div style="font-size:12px;font-weight:600;color:#fff;margin-bottom:4px;">Great Barrier Reef Explorer</div>
                        <div style="font-size:11px;color:rgba(255,255,255,0.35);">5 legs · Queensland, Australia</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- ─────────────────────────────────────────── --}}
{{-- ADVANCED FLIGHT TRACKING                     --}}
{{-- ─────────────────────────────────────────── --}}
<section style="padding:80px 0;border-top:1px solid rgba(255,255,255,0.07);">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1.8fr;gap:48px;align-items:start;">

            {{-- Left text --}}
            <div>
                <div class="card-label" style="font-size:10px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,0.3);margin-bottom:10px;">POWERED BY ACARS</div>
                <h2 style="font-size:32px;font-weight:800;color:#fff;letter-spacing:-0.5px;line-height:1.2;margin-bottom:16px;">Advanced Flight Tracking</h2>
                <p style="font-size:14px;color:rgba(255,255,255,0.4);line-height:1.7;">Every flight recorded with precision. Real-time position, altitude, speed, and a full activity log — all tracked automatically.</p>

                {{-- Activity log --}}
                <div style="margin-top:32px;">
                    <div style="font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,0.25);margin-bottom:12px;">FLIGHT ACTIVITY</div>
                    @php $activities = [
                        ['✈','Departed YSSY runway 34L','22:02'],
                        ['↗','Cruising at FL380','22:35'],
                        ['↘','Descending, 141nm to go','22:32'],
                        ['◎','On Final, 93ft, 4nm out','22:48'],
                        ['⏬','Touchdown -77fpm, 0.99g','22:51'],
                        ['✅','PIREP Accepted','22:53'],
                    ]; @endphp
                    @foreach($activities as $a)
                    <div class="activity-row">
                        <span class="activity-icon">{{ $a[0] }}</span>
                        <span class="activity-text">{{ $a[1] }}</span>
                        <span class="activity-time">{{ $a[2] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Right: Live flight cards --}}
            <div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                    @forelse($mappedFlights->take(3) as $f)
                    @php
                        $phase = strtoupper($f['phase'] ?? 'ENROUTE');
                        $phaseClass = match(strtolower($f['phase'] ?? '')) {
                            'cruise','enroute' => 'phase-cruise',
                            'descent','descending' => 'phase-descent',
                            'boarding','boarding' => 'phase-boarding',
                            default => 'phase-default'
                        };
                    @endphp
                    <div class="flight-card">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:12px;font-weight:700;color:#fff;font-family:'JetBrains Mono',monospace;">{{ $f['flight_number'] }}</span>
                            <span class="phase-badge {{ $phaseClass }}">{{ $phase }}</span>
                        </div>
                        <div class="route-line">
                            <span class="route-icao">{{ $f['departure'] }}</span>
                            <div class="route-arrow">
                                <div class="route-dash"></div>
                                <span class="route-plane">✈</span>
                                <div class="route-dash" style="background:linear-gradient(90deg,rgba(220,38,38,0.2),rgba(220,38,38,0.6));"></div>
                            </div>
                            <span class="route-icao">{{ $f['arrival'] }}</span>
                        </div>
                        <div class="flight-meta">
                            <div class="meta-item"><span class="key">ALT</span><span class="val">{{ $f['altitude'] > 0 ? 'FL'.intdiv($f['altitude'],100) : 'SFC' }}</span></div>
                            <div class="meta-item"><span class="key">SPEED</span><span class="val">{{ $f['ground_speed'] }}kt</span></div>
                        </div>
                    </div>
                    @empty
                    {{-- Placeholder flight cards --}}
                    @php $placeholders = [
                        ['FVA001','SBGR','SBSP','CRUISE','FL380','440kt'],
                        ['FVA042','EGLL','KJFK','ENROUTE','FL350','480kt'],
                        ['FVA117','KLAX','YSSY','DESCENT','FL120','310kt'],
                    ]; @endphp
                    @foreach($placeholders as $p)
                    <div class="flight-card">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:12px;font-weight:700;color:#fff;font-family:'JetBrains Mono',monospace;">{{ $p[0] }}</span>
                            <span class="phase-badge {{ $p[3]==='CRUISE'?'phase-cruise':($p[3]==='DESCENT'?'phase-descent':'phase-enroute') }}">{{ $p[3] }}</span>
                        </div>
                        <div class="route-line">
                            <span class="route-icao">{{ $p[1] }}</span>
                            <div class="route-arrow">
                                <div class="route-dash"></div>
                                <span class="route-plane">✈</span>
                                <div class="route-dash" style="background:linear-gradient(90deg,rgba(220,38,38,0.2),rgba(220,38,38,0.6));"></div>
                            </div>
                            <span class="route-icao">{{ $p[2] }}</span>
                        </div>
                        <div class="flight-meta">
                            <div class="meta-item"><span class="key">ALT</span><span class="val">{{ $p[4] }}</span></div>
                            <div class="meta-item"><span class="key">SPEED</span><span class="val">{{ $p[5] }}</span></div>
                        </div>
                    </div>
                    @endforeach
                    @endforelse
                </div>

                {{-- Live Map preview --}}
                <div style="margin-top:12px;background:#0d0d0d;border:1px solid rgba(255,255,255,0.07);border-radius:12px;overflow:hidden;height:240px;position:relative;" id="mini-map-wrap">
                    <div id="hero-livemap" style="width:100%;height:100%;"></div>
                    <div style="position:absolute;top:12px;left:12px;background:rgba(10,10,10,0.8);border:1px solid rgba(255,255,255,0.1);border-radius:6px;padding:6px 10px;font-size:11px;color:rgba(255,255,255,0.6);font-family:'JetBrains Mono',monospace;z-index:10;">
                        <span style="display:inline-block;width:6px;height:6px;background:#22c55e;border-radius:50%;margin-right:6px;vertical-align:middle;"></span>LIVE · {{ count($mappedFlights) }} aircraft
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- ─────────────────────────────────────────── --}}
{{-- PLATFORM STATS                               --}}
{{-- ─────────────────────────────────────────── --}}
<section id="stats" style="padding:80px 0;border-top:1px solid rgba(255,255,255,0.07);">
    <div class="container">
        <div style="display:grid;grid-template-columns:1.4fr 3fr;gap:48px;align-items:center;">
            <div>
                <div class="card-label" style="font-size:10px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,0.3);margin-bottom:10px;">PLATFORM</div>
                <h2 style="font-size:28px;font-weight:800;color:#fff;letter-spacing:-0.5px;line-height:1.2;margin-bottom:14px;">Built for pilots who take it seriously</h2>
                <p style="font-size:13px;color:rgba(255,255,255,0.4);line-height:1.7;">From real-world schedules to innovative tools, every detail is crafted to elevate your simulation experience.</p>
            </div>

            <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;">
                @php $stats = [
                    ['svg_pilots',   'Pilots',       number_format($pilotCount)],
                    ['svg_sched',    'Schedules',    number_format($scheduleCount)],
                    ['svg_pirep',    'PIREPs',       '—'],
                    ['svg_hours',    'Hours Flown',  '—'],
                    ['svg_fleet',    'Fleet',        number_format($aircraftCount)],
                ]; @endphp
                @foreach($stats as $s)
                <div class="stat-cell">
                    <div class="stat-icon">
                        @if($s[0]==='svg_pilots')
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                        @elseif($s[0]==='svg_sched')
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        @elseif($s[0]==='svg_pirep')
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10,9 9,9 8,9"/></svg>
                        @elseif($s[0]==='svg_hours')
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
                        @else
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                        @endif
                    </div>
                    <div class="stat-label">{{ $s[1] }}</div>
                    <div class="stat-num">{{ $s[2] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>


{{-- ─────────────────────────────────────────── --}}
{{-- LIVE MAP FULL                                --}}
{{-- ─────────────────────────────────────────── --}}
<section id="live-map" style="padding:80px 0;border-top:1px solid rgba(255,255,255,0.07);">
    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:32px;">
            <div>
                <span class="section-label">Live Operations</span>
                <h2 class="section-title" style="font-size:32px;">Active Fleet Map</h2>
            </div>
            <a href="{{ route('live-map') }}" class="btn-outline" style="font-size:13px;padding:9px 18px;">Open Live Map →</a>
        </div>

        <div style="display:grid;grid-template-columns:1fr 340px;gap:16px;">
            {{-- Map --}}
            <div style="background:#0d0d0d;border:1px solid rgba(255,255,255,0.07);border-radius:14px;overflow:hidden;height:480px;position:relative;">
                <div id="welcome-livemap" style="width:100%;height:100%;"></div>
            </div>

            {{-- Flight list --}}
            <div class="dark-card" style="display:flex;flex-direction:column;overflow:hidden;height:480px;">
                <div style="padding:16px 20px;border-bottom:1px solid rgba(255,255,255,0.07);display:flex;justify-content:space-between;align-items:center;flex-shrink:0;">
                    <span style="font-size:13px;font-weight:700;color:#fff;">Live Flights</span>
                    <span style="font-size:11px;font-weight:600;color:#22c55e;background:rgba(34,197,94,0.1);padding:3px 8px;border-radius:4px;">{{ count($mappedFlights) }} online</span>
                </div>
                <div style="flex:1;overflow-y:auto;">
                    @forelse($mappedFlights as $f)
                    <div onclick="focusFlight({{ $f['id'] }})" style="padding:14px 20px;border-bottom:1px solid rgba(255,255,255,0.05);cursor:pointer;transition:background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
                        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                            <span style="font-size:13px;font-weight:700;color:#fff;font-family:'JetBrains Mono',monospace;">{{ $f['flight_number'] }}</span>
                            <span style="font-size:10px;color:rgba(255,255,255,0.3);">{{ $f['pilot_name'] }}</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                            <span style="font-size:14px;font-weight:800;color:#fff;">{{ $f['departure'] }}</span>
                            <div style="flex:1;height:1px;background:linear-gradient(90deg,rgba(220,38,38,0.5),rgba(220,38,38,0.2));position:relative;">
                                <span style="position:absolute;top:-6px;left:50%;transform:translateX(-50%);font-size:10px;color:#dc2626;">✈</span>
                            </div>
                            <span style="font-size:14px;font-weight:800;color:#fff;">{{ $f['arrival'] }}</span>
                        </div>
                        <div style="display:flex;gap:12px;font-size:11px;font-family:'JetBrains Mono',monospace;color:rgba(255,255,255,0.35);">
                            <span>{{ $f['altitude'] > 0 ? 'FL'.intdiv($f['altitude'],100) : 'GND' }}</span>
                            <span>{{ $f['ground_speed'] }}kt</span>
                            <span>{{ $f['heading'] }}°</span>
                            <span style="margin-left:auto;color:rgba(255,255,255,0.2);">{{ $f['phase'] }}</span>
                        </div>
                    </div>
                    @empty
                    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;text-align:center;padding:32px;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="1.5" style="margin-bottom:12px;"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <p style="font-size:13px;color:rgba(255,255,255,0.25);">No active flights</p>
                        <p style="font-size:12px;color:rgba(255,255,255,0.15);margin-top:4px;">ACARS connection idle</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>


{{-- ─────────────────────────────────────────── --}}
{{-- SCHEDULES                                    --}}
{{-- ─────────────────────────────────────────── --}}
<section id="schedules" style="padding:80px 0;border-top:1px solid rgba(255,255,255,0.07);">
    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:32px;">
            <div>
                <span class="section-label">Ready to Fly</span>
                <h2 class="section-title" style="font-size:32px;">Featured Schedules</h2>
            </div>
            @auth
                <a href="{{ route('flights') }}" class="btn-outline" style="font-size:13px;padding:9px 18px;">Browse all →</a>
            @endauth
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
            @forelse($schedules as $s)
            <div class="dark-card" style="padding:22px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <span style="font-size:13px;font-weight:700;color:#fff;font-family:'JetBrains Mono',monospace;">{{ $s->flight_number }}</span>
                    <span style="font-size:10px;background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.4);padding:3px 8px;border-radius:4px;font-family:'JetBrains Mono',monospace;text-transform:uppercase;">{{ $s->aircraft_type ?? 'A320' }}</span>
                </div>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                    <div>
                        <div style="font-size:22px;font-weight:900;color:#fff;letter-spacing:-0.5px;line-height:1;">{{ $s->departure }}</div>
                        <div style="font-size:9px;color:rgba(255,255,255,0.25);text-transform:uppercase;letter-spacing:1px;margin-top:2px;">Departure</div>
                    </div>
                    <div style="flex:1;display:flex;align-items:center;gap:4px;">
                        <div style="flex:1;height:1px;background:rgba(255,255,255,0.1);"></div>
                        <span style="color:#dc2626;font-size:12px;">✈</span>
                        <div style="flex:1;height:1px;background:rgba(255,255,255,0.1);"></div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:22px;font-weight:900;color:#fff;letter-spacing:-0.5px;line-height:1;">{{ $s->arrival }}</div>
                        <div style="font-size:9px;color:rgba(255,255,255,0.25);text-transform:uppercase;letter-spacing:1px;margin-top:2px;">Arrival</div>
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding-top:14px;border-top:1px solid rgba(255,255,255,0.06);">
                    <span style="font-size:11px;color:rgba(255,255,255,0.3);">Est. {{ number_format($s->flight_time,1) }}h</span>
                    @auth
                        <a href="{{ route('flights') }}" class="btn-red" style="font-size:11px;padding:6px 14px;">Book →</a>
                    @else
                        <a href="{{ route('login') }}" style="font-size:11px;color:#dc2626;text-decoration:none;font-weight:600;">Login →</a>
                    @endauth
                </div>
            </div>
            @empty
            <div style="grid-column:span 3;text-align:center;padding:48px;color:rgba(255,255,255,0.2);font-size:14px;">No schedules available yet.</div>
            @endforelse
        </div>
    </div>
</section>


{{-- ─────────────────────────────────────────── --}}
{{-- LATEST LANDINGS                              --}}
{{-- ─────────────────────────────────────────── --}}
<section style="padding:80px 0;border-top:1px solid rgba(255,255,255,0.07);">
    <div class="container">
        <div style="margin-bottom:32px;">
            <span class="section-label">Pilot Reports</span>
            <h2 class="section-title" style="font-size:32px;">Latest Landings</h2>
        </div>

        <div class="dark-card" style="overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.07);">
                        <th style="padding:14px 20px;text-align:left;font-size:10px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,0.25);">Pilot</th>
                        <th style="padding:14px 20px;text-align:left;font-size:10px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,0.25);">Flight</th>
                        <th style="padding:14px 20px;text-align:left;font-size:10px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,0.25);">Route</th>
                        <th style="padding:14px 20px;text-align:left;font-size:10px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,0.25);">Aircraft</th>
                        <th style="padding:14px 20px;text-align:right;font-size:10px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,0.25);">Landing Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestLandings as $l)
                    @php
                        $rate = (int)($l->landing_rate ?? 0);
                        $rateColor = '#22c55e';
                        if($rate < -500 || $rate > 0) $rateColor = '#ef4444';
                        elseif($rate < -300) $rateColor = '#f59e0b';
                    @endphp
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                        <td style="padding:14px 20px;">
                            <div style="font-weight:600;color:#fff;">{{ $l->user?->name ?? 'Unknown' }}</div>
                            <div style="font-size:11px;color:rgba(255,255,255,0.3);font-family:'JetBrains Mono',monospace;">{{ $l->user?->pilot_id ?? '—' }}</div>
                        </td>
                        <td style="padding:14px 20px;font-family:'JetBrains Mono',monospace;font-weight:600;color:#fff;">{{ $l->flight_number }}</td>
                        <td style="padding:14px 20px;font-family:'JetBrains Mono',monospace;color:rgba(255,255,255,0.6);">{{ $l->departure }} → {{ $l->arrival }}</td>
                        <td style="padding:14px 20px;color:rgba(255,255,255,0.4);font-size:12px;">{{ $l->aircraft_icao ?? '—' }}</td>
                        <td style="padding:14px 20px;text-align:right;">
                            <span style="font-family:'JetBrains Mono',monospace;font-weight:700;color:{{ $rateColor }};">{{ $l->landing_rate ? $l->landing_rate.' fpm' : '—' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="padding:48px;text-align:center;color:rgba(255,255,255,0.2);">No landing reports yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>


{{-- ─────────────────────────────────────────── --}}
{{-- TESTIMONIALS                                 --}}
{{-- ─────────────────────────────────────────── --}}
<section style="padding:80px 0;border-top:1px solid rgba(255,255,255,0.07);">
    <div class="container">
        <div style="text-align:center;margin-bottom:56px;">
            <span class="section-label">Don't take our word for it</span>
            <h2 class="section-title">Hear what our pilots have to say</h2>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
            @php $testimonials = [
                ['The FlyAway staff and community alike are superb and offer amazing support for pilots of all levels. That, paired with an immaculate user interface flooded with realistic flights, awesome tours, and wonderful flight scheduling and tracking — by far the best VA out there!', 'Aaron H.', 'FVA9237'],
                ['Having flown with various VAs and pilot career apps over the past 30+ years, I\'ve finally found one that is friendly, relaxed yet professional with a great management team and community behind it. There are plenty of aircraft and routes for you to enjoy in any part of the world.', 'Henryk S.', 'FVA6738'],
                ['I thoroughly enjoy the extensive range of realistic flight experiences that FlyAway provides. The ACARS tracking is second to none and the community is incredibly welcoming to new members.', 'Jeff A.', 'FVA3188'],
            ]; @endphp
            @foreach($testimonials as $t)
            <div class="testimonial-card">
                <div>
                    <div class="stars">★★★★★</div>
                    <span style="font-size:11px;color:rgba(255,255,255,0.3);margin-left:8px;">(5.0)</span>
                </div>
                <p class="testi-text">'{{ $t[0] }}'</p>
                <div class="testi-author">
                    <div class="testi-avatar">{{ substr($t[1],0,2) }}</div>
                    <div>
                        <div class="testi-name">{{ $t[1] }}</div>
                        <div class="testi-id">{{ $t[2] }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ─────────────────────────────────────────── --}}
{{-- CTA FINAL                                    --}}
{{-- ─────────────────────────────────────────── --}}
<section style="padding:100px 0;border-top:1px solid rgba(255,255,255,0.07);text-align:center;position:relative;overflow:hidden;">
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:600px;height:300px;background:radial-gradient(ellipse,rgba(220,38,38,0.07),transparent 70%);pointer-events:none;"></div>
    <div class="container" style="position:relative;z-index:1;">
        <h2 class="section-title" style="margin-bottom:18px;">Ready to start flying?</h2>
        <p class="section-sub" style="margin:0 auto 36px;">Join thousands of pilots already flying with FlyAway Virtual. Create your free account and take to the skies today.</p>
        <div style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
            @auth
                <a href="{{ route('flights') }}" class="btn-red">Find your next flight →</a>
            @else
                <a href="{{ route('register') }}" class="btn-red">Create account →</a>
                <a href="{{ route('login') }}" class="btn-outline">Sign in</a>
            @endauth
        </div>
    </div>
</section>


{{-- ─────────────────────────────────────────── --}}
{{-- FOOTER                                       --}}
{{-- ─────────────────────────────────────────── --}}
<footer style="border-top:1px solid rgba(255,255,255,0.07);padding:40px 0;background:#0a0a0a;">
    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:28px;height:28px;background:#dc2626;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </div>
                <span style="font-size:15px;font-weight:800;color:#fff;">FLY<span style="color:#dc2626;">AWAY</span></span>
            </div>
            <p style="font-size:12px;color:rgba(255,255,255,0.2);">&copy; {{ date('Y') }} FlyAway Virtual Airline. For flight simulation only.</p>
            <div style="display:flex;gap:24px;">
                <a href="#" class="footer-link">Terms</a>
                <a href="#" class="footer-link">Privacy</a>
                <a href="https://discord.gg" target="_blank" class="footer-link" style="color:#dc2626;">Discord</a>
            </div>
        </div>
    </div>
</footer>


{{-- ─────────────────────────────────────────── --}}
{{-- LEAFLET JS                                   --}}
{{-- ─────────────────────────────────────────── --}}
<script>
const FLIGHTS = @json($mappedFlights);
let mainMap, miniMap;
const mainMarkers = {}, mainPaths = {};

function planeIcon(heading) {
    return L.divIcon({
        html: `<div style="transform:rotate(${heading}deg);line-height:1;filter:drop-shadow(0 2px 6px rgba(220,38,38,0.5));">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="#dc2626">
                <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L14 19v-5.5L21 16z"/>
            </svg>
        </div>`,
        className: '',
        iconSize: [22, 22],
        iconAnchor: [11, 11],
        popupAnchor: [0, -14]
    });
}

function cartoTile(map) {
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19, subdomains: 'abcd', attribution: ''
    }).addTo(map);
}

function addFlights(map, markers, paths) {
    FLIGHTS.forEach(f => {
        const pos = [f.current_lat, f.current_lng];
        if (f.breadcrumbs && f.breadcrumbs.length > 1) {
            paths[f.id] = L.polyline([...f.breadcrumbs, pos], {
                color: '#dc2626', weight: 2, opacity: 0.5, dashArray: '4,4'
            }).addTo(map);
        }
        const m = L.marker(pos, { icon: planeIcon(f.heading) }).addTo(map);
        m.bindPopup(`
            <div style="font-family:Inter,sans-serif;min-width:200px;padding:4px;">
                <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(255,255,255,0.08);padding-bottom:8px;margin-bottom:8px;">
                    <span style="font-size:14px;font-weight:800;color:#fff;font-family:'JetBrains Mono',monospace;">${f.flight_number}</span>
                    <span style="font-size:9px;background:rgba(220,38,38,0.15);color:#dc2626;padding:2px 6px;border-radius:4px;text-transform:uppercase;font-weight:700;">${f.phase||'Enroute'}</span>
                </div>
                <div style="font-size:11px;color:rgba(255,255,255,0.5);margin-bottom:6px;">👨‍✈️ ${f.pilot_name} &middot; ${f.pilot_id}</div>
                <div style="font-size:13px;font-weight:800;color:#fff;margin-bottom:8px;">${f.departure} → ${f.arrival}</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;font-size:11px;font-family:'JetBrains Mono',monospace;">
                    <div><span style="color:rgba(255,255,255,0.3);">ALT </span><strong style="color:#fff;">${f.altitude>0?f.altitude.toLocaleString()+' ft':'SFC'}</strong></div>
                    <div><span style="color:rgba(255,255,255,0.3);">SPD </span><strong style="color:#fff;">${f.ground_speed} kt</strong></div>
                    <div><span style="color:rgba(255,255,255,0.3);">HDG </span><strong style="color:#fff;">${f.heading}°</strong></div>
                    <div><span style="color:rgba(255,255,255,0.3);">ACFT </span><strong style="color:#fff;">${f.aircraft_icao||'—'}</strong></div>
                </div>
            </div>
        `);
        markers[f.id] = m;
    });
    if (FLIGHTS.length > 0) {
        const g = L.featureGroup(Object.values(markers));
        map.fitBounds(g.getBounds().pad(0.2));
    }
}

window.focusFlight = function(id) {
    const m = mainMarkers[id];
    if (m && mainMap) {
        mainMap.setView(m.getLatLng(), Math.max(mainMap.getZoom(), 7), { animate: true, duration: 1.1 });
        setTimeout(() => m.openPopup(), 500);
        document.getElementById('live-map').scrollIntoView({ behavior: 'smooth' });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // Main map
    mainMap = L.map('welcome-livemap', { center: [20, 0], zoom: 2, zoomControl: true, attributionControl: false });
    cartoTile(mainMap);
    addFlights(mainMap, mainMarkers, mainPaths);

    // Mini map in hero
    const mm = document.getElementById('hero-livemap');
    if (mm) {
        miniMap = L.map('hero-livemap', { center: [20, 0], zoom: 2, zoomControl: false, attributionControl: false, dragging: false, scrollWheelZoom: false });
        cartoTile(miniMap);
        const miniMarkers = {}, miniPaths = {};
        addFlights(miniMap, miniMarkers, miniPaths);
    }
});
</script>
</body>
</html>
