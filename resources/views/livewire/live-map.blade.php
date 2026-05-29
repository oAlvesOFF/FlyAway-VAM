<?php

use App\Models\ActiveFlight;
use Livewire\Volt\Component;

new class extends Component {
    public $flights = [];

    public function mount(): void
    {
        $this->loadFlights();
    }

    public function loadFlights(): void
    {
        $flights = ActiveFlight::active()
            ->with('user')
            ->orderBy('position_updated_at', 'desc')
            ->get();

        $this->flights = $flights->map(function ($f) {
            $recent = \App\Models\FlightPosition::where('active_flight_id', $f->id)
                ->orderBy('recorded_at')
                ->get(['latitude', 'longitude'])
                ->map(fn($pos) => [(float) $pos->latitude, (float) $pos->longitude])
                ->toArray();

            return [
                'id' => $f->id,
                'flight_number' => $f->flight_number,
                'pilot_name' => $f->user?->name ?? 'Unknown',
                'pilot_id' => $f->user?->pilot_id ?? '',
                'aircraft_registration' => $f->aircraft_registration,
                'aircraft_icao' => $f->aircraft_icao,
                'aircraft_type' => $f->aircraft_type,
                'departure' => $f->departure,
                'arrival' => $f->arrival,
                'departure_lat' => (float) $f->departure_lat,
                'departure_lng' => (float) $f->departure_lng,
                'arrival_lat' => (float) $f->arrival_lat,
                'arrival_lng' => (float) $f->arrival_lng,
                'current_lat' => (float) $f->current_lat,
                'current_lng' => (float) $f->current_lng,
                'heading' => $f->heading,
                'altitude' => $f->altitude,
                'ground_speed' => $f->ground_speed,
                'phase' => $f->phase,
                'started_at' => $f->started_at?->diffForHumans(),
                'position_updated_at' => $f->position_updated_at?->diffForHumans(),
                'breadcrumbs' => $recent,
            ];
        })->values()->toArray();

        $this->dispatch('flightsUpdated', flights: $this->flights);
    }
}; ?>

<div class="livewire-map-container">
<div class="space-y-6">
    <!-- Map Section -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-4">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">Live Flight Map</h1>
                <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 text-xs font-bold">{{ count($flights) }} Active</span>
            </div>
        </div>
        <div class="relative rounded-xl overflow-hidden" style="height: 500px;">
            <div id="livemap" class="w-full h-full z-0"></div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                ✈ Live Flights
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-3">Pilot</th>
                        <th class="px-6 py-3">Flight</th>
                        <th class="px-6 py-3">Route</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Altitude</th>
                        <th class="px-6 py-3">Speed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($flights as $f)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer" onclick="focusFlight({{ $f['id'] }})">
                        <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">{{ $f['pilot_name'] }}</td>
                        <td class="px-6 py-4">{{ $f['flight_number'] }}</td>
                        <td class="px-6 py-4">{{ $f['departure'] }} → {{ $f['arrival'] }}</td>
                        <td class="px-6 py-4 capitalize">{{ $f['phase'] }}</td>
                        <td class="px-6 py-4">{{ number_format($f['altitude']) }} ft</td>
                        <td class="px-6 py-4">{{ $f['ground_speed'] }} kts</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">No active flights</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Flight Details Side Panel -->
<div id="flight-details-panel" class="flight-panel-overlay" onclick="closeFlight(event)">
    <div class="flight-panel" id="flight-panel-inner">
        <!-- Header -->
        <div class="fp-header">
            <div class="fp-airline-logo">
                <span class="fp-airline-initials" id="fp-initials">FA</span>
            </div>
            <div class="fp-header-info">
                <div class="fp-flight-number" id="fp-flight-number">—</div>
                <div class="fp-pilot-name" id="fp-pilot-name">—</div>
            </div>
            <button class="fp-close-btn" onclick="closeFlight()">✕</button>
        </div>

        <!-- Route -->
        <div class="fp-route">
            <div class="fp-airport">
                <div class="fp-airport-code" id="fp-dep">—</div>
                <div class="fp-airport-label" id="fp-dep-label">Departure</div>
            </div>
            <div class="fp-route-arrow">
                <div class="fp-route-line"></div>
                <span class="fp-plane-mid">✈</span>
                <div class="fp-route-line"></div>
            </div>
            <div class="fp-airport fp-airport-right">
                <div class="fp-airport-code" id="fp-arr">—</div>
                <div class="fp-airport-label" id="fp-arr-label">Arrival</div>
            </div>
        </div>

        <!-- Flight Status -->
        <div class="fp-section">
            <div class="fp-section-header">
                <span>FLIGHT STATUS</span>
                <span class="fp-status-badge" id="fp-phase-badge">—</span>
            </div>
            <div class="fp-stats-grid">
                <div class="fp-stat">
                    <div class="fp-stat-value" id="fp-altitude">—</div>
                    <div class="fp-stat-label">ALTITUDE</div>
                </div>
                <div class="fp-stat">
                    <div class="fp-stat-value" id="fp-speed">—</div>
                    <div class="fp-stat-label">SPEED</div>
                </div>
                <div class="fp-stat">
                    <div class="fp-stat-value" id="fp-heading">—</div>
                    <div class="fp-stat-label">HEADING</div>
                </div>
            </div>
        </div>

        <!-- Pilot In Command -->
        <div class="fp-section">
            <div class="fp-section-title">PILOT IN COMMAND</div>
            <div class="fp-pilot-card">
                <div class="fp-pilot-details">
                    <div class="fp-pilot-card-name" id="fp-pilot-card-name">—</div>
                    <div class="fp-pilot-id" id="fp-pilot-id">—</div>
                </div>
            </div>
            <div class="fp-stats-grid mt-2">
                <div class="fp-stat">
                    <div class="fp-stat-value" id="fp-started">—</div>
                    <div class="fp-stat-label">STARTED</div>
                </div>
                <div class="fp-stat">
                    <div class="fp-stat-value" id="fp-updated">—</div>
                    <div class="fp-stat-label">LAST UPDATE</div>
                </div>
            </div>
        </div>

        <!-- Aircraft -->
        <div class="fp-section" id="fp-aircraft-section">
            <div class="fp-section-title">AIRCRAFT</div>
            <div class="fp-aircraft-card">
                <div class="fp-aircraft-type" id="fp-aircraft-type">—</div>
                <div class="fp-aircraft-reg" id="fp-aircraft-reg">—</div>
            </div>
        </div>

        <!-- Current Position -->
        <div class="fp-section">
            <div class="fp-section-title">CURRENT POSITION</div>
            <div class="fp-stats-grid">
                <div class="fp-stat">
                    <div class="fp-stat-value" id="fp-lat">—</div>
                    <div class="fp-stat-label">LATITUDE</div>
                </div>
                <div class="fp-stat">
                    <div class="fp-stat-value" id="fp-lng">—</div>
                    <div class="fp-stat-label">LONGITUDE</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
#livemap { width: 100%; height: 100%; position: relative; }

/* ── Flight Details Panel ── */
.flight-panel-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(3px);
    display: flex;
    align-items: center;
    justify-content: flex-end;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}
.flight-panel-overlay.open {
    opacity: 1;
    pointer-events: all;
}
.flight-panel {
    width: 340px;
    max-width: 95vw;
    height: 100vh;
    overflow-y: auto;
    background: #0f172a;
    color: #e2e8f0;
    display: flex;
    flex-direction: column;
    gap: 0;
    transform: translateX(100%);
    transition: transform 0.35s cubic-bezier(0.4,0,0.2,1);
    box-shadow: -8px 0 40px rgba(0,0,0,0.5);
}
.flight-panel-overlay.open .flight-panel {
    transform: translateX(0);
}

/* Header */
.fp-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 20px 16px;
    border-bottom: 1px solid #1e293b;
}
.fp-airline-logo {
    width: 48px; height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.fp-airline-initials {
    font-size: 16px; font-weight: 800; color: #fff; letter-spacing: 1px;
}
.fp-header-info { flex: 1; min-width: 0; }
.fp-flight-number {
    font-size: 20px; font-weight: 800; color: #f8fafc; line-height: 1.2;
}
.fp-pilot-name {
    font-size: 13px; color: #94a3b8; margin-top: 2px;
}
.fp-close-btn {
    background: #1e293b; border: none; color: #94a3b8;
    width: 32px; height: 32px; border-radius: 8px;
    cursor: pointer; font-size: 14px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.2s, color 0.2s;
}
.fp-close-btn:hover { background: #ef4444; color: #fff; }

/* Route */
.fp-route {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px;
    background: #1e293b;
    gap: 8px;
}
.fp-airport { text-align: center; }
.fp-airport-right { text-align: center; }
.fp-airport-code {
    font-size: 20px; font-weight: 800; color: #f8fafc; letter-spacing: 1px;
}
.fp-airport-label {
    font-size: 11px; color: #64748b; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90px;
}
.fp-route-arrow {
    display: flex; align-items: center; flex: 1; gap: 4px;
}
.fp-route-line {
    flex: 1; height: 1px; background: #334155;
}
.fp-plane-mid { font-size: 18px; color: #3b82f6; }

/* Sections */
.fp-section {
    padding: 16px 20px;
    border-bottom: 1px solid #1e293b;
}
.fp-section-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 12px;
    font-size: 11px; font-weight: 700; letter-spacing: 0.08em; color: #64748b;
}
.fp-section-title {
    font-size: 11px; font-weight: 700; letter-spacing: 0.08em; color: #64748b;
    margin-bottom: 12px;
}
.fp-status-badge {
    padding: 3px 10px;
    border-radius: 999px;
    background: #22c55e22;
    color: #22c55e;
    font-size: 11px; font-weight: 700;
    text-transform: capitalize;
    border: 1px solid #22c55e44;
}

/* Stats Grid */
.fp-stats-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
}
.fp-stats-grid.mt-2 { margin-top: 10px; grid-template-columns: repeat(2, 1fr); }
.fp-stat {
    background: #1e293b; border-radius: 10px; padding: 10px 8px; text-align: center;
}
.fp-stat-value {
    font-size: 16px; font-weight: 700; color: #f8fafc; line-height: 1.2;
}
.fp-stat-label {
    font-size: 10px; font-weight: 600; color: #64748b; letter-spacing: 0.06em; margin-top: 4px;
}

/* Pilot card */
.fp-pilot-card {
    background: #1e293b; border-radius: 10px; padding: 12px 14px;
    display: flex; align-items: center; gap: 10px;
}
.fp-pilot-card-name {
    font-size: 15px; font-weight: 700; color: #f8fafc;
}
.fp-pilot-id {
    font-size: 12px; color: #64748b; margin-top: 2px;
}

/* Aircraft */
.fp-aircraft-card {
    background: #1e293b; border-radius: 10px; padding: 12px 14px;
}
.fp-aircraft-type {
    font-size: 16px; font-weight: 700; color: #f8fafc;
}
.fp-aircraft-reg {
    font-size: 12px; color: #64748b; margin-top: 2px; letter-spacing: 0.05em;
}
</style>
@endpush

<script>
document.addEventListener('livewire:navigated', function() {
    if (window._livemapInitialized) return;
    window._livemapInitialized = true;

    let map;
    const flightMarkers = {};
    const routeLines = {};
    let allFlights = [];

    function initMap() {
        if (document.getElementById('livemap')) {
            map = L.map('livemap', {
                center: [20, 0],
                zoom: 2,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map);
        }
    }

    function createPlaneIcon(heading) {
        return L.divIcon({
            html: `<div style="transform:rotate(${heading}deg); font-size: 26px; filter:drop-shadow(0 2px 4px rgba(0,0,0,0.3));">✈</div>`,
            className: '',
            iconSize: [26, 26],
            iconAnchor: [13, 13],
        });
    }

    function openFlightPanel(f) {
        const panel = document.getElementById('flight-details-panel');

        // Get airline initials from flight number
        const initials = f.flight_number ? f.flight_number.replace(/[0-9]/g, '').substring(0,3) : 'FA';
        document.getElementById('fp-initials').textContent = initials || 'FA';
        document.getElementById('fp-flight-number').textContent = f.flight_number || '—';
        document.getElementById('fp-pilot-name').textContent = f.pilot_name || '—';

        document.getElementById('fp-dep').textContent = f.departure || '—';
        document.getElementById('fp-arr').textContent = f.arrival || '—';

        // Phase badge color
        const badge = document.getElementById('fp-phase-badge');
        badge.textContent = f.phase || '—';
        const phaseColors = {
            'boarding': '#f59e0b', 'preflight': '#f59e0b', 'taxiing': '#f59e0b',
            'takeoff': '#3b82f6', 'climb': '#3b82f6', 'cruise': '#22c55e',
            'descent': '#f97316', 'approach': '#ef4444', 'landing': '#ef4444',
            'landed': '#8b5cf6', 'arrived': '#8b5cf6'
        };
        const c = phaseColors[f.phase?.toLowerCase()] || '#22c55e';
        badge.style.background = c + '22';
        badge.style.color = c;
        badge.style.borderColor = c + '44';

        document.getElementById('fp-altitude').textContent = f.altitude > 0 ? f.altitude.toLocaleString() + 'ft' : 'SFC';
        document.getElementById('fp-speed').textContent = (f.ground_speed || 0) + 'kts';
        document.getElementById('fp-heading').textContent = (f.heading || 0) + 'º';

        document.getElementById('fp-pilot-card-name').textContent = f.pilot_name || '—';
        document.getElementById('fp-pilot-id').textContent = f.pilot_id ? '(' + f.pilot_id + ')' : '';

        document.getElementById('fp-started').textContent = f.started_at || '—';
        document.getElementById('fp-updated').textContent = f.position_updated_at || '—';

        document.getElementById('fp-aircraft-type').textContent = f.aircraft_type || f.aircraft_icao || '—';
        document.getElementById('fp-aircraft-reg').textContent = f.aircraft_registration || '—';

        document.getElementById('fp-lat').textContent = f.current_lat ? f.current_lat.toFixed(4) + 'º' : '—';
        document.getElementById('fp-lng').textContent = f.current_lng ? f.current_lng.toFixed(4) + 'º' : '—';

        panel.classList.add('open');
    }

    window.closeFlight = function(event) {
        if (event && event.target !== document.getElementById('flight-details-panel')) return;
        document.getElementById('flight-details-panel').classList.remove('open');
    };

    function renderFlights(flights) {
        if (!map) return;
        allFlights = flights;

        const currentIds = new Set(flights.map(f => f.id));

        // Remove stale markers and routes
        Object.keys(flightMarkers).forEach(id => {
            if (!currentIds.has(parseInt(id))) {
                map.removeLayer(flightMarkers[id]);
                delete flightMarkers[id];
                if (routeLines[id]) {
                    map.removeLayer(routeLines[id]);
                    delete routeLines[id];
                }
            }
        });

        flights.forEach(f => {
            const pos = [f.current_lat, f.current_lng];

            // Draw breadcrumb trail if available
            if (f.breadcrumbs && f.breadcrumbs.length > 1) {
                if (!routeLines[f.id]) {
                    routeLines[f.id] = L.polyline(f.breadcrumbs, {
                        color: '#3b82f6',
                        weight: 2,
                        opacity: 0.5,
                    }).addTo(map);
                } else {
                    routeLines[f.id].setLatLngs(f.breadcrumbs);
                }
            }

            if (!flightMarkers[f.id]) {
                const marker = L.marker(pos, { icon: createPlaneIcon(f.heading) }).addTo(map);
                marker.on('click', function() {
                    openFlightPanel(f);
                    map.setView(marker.getLatLng(), Math.max(map.getZoom(), 7), { animate: true });
                });
                flightMarkers[f.id] = marker;
            } else {
                flightMarkers[f.id].setLatLng(pos);
                flightMarkers[f.id].setIcon(createPlaneIcon(f.heading));
                flightMarkers[f.id].off('click');
                const _marker = flightMarkers[f.id];
                flightMarkers[f.id].on('click', function() {
                    openFlightPanel(f);
                    map.setView(_marker.getLatLng(), Math.max(map.getZoom(), 7), { animate: true });
                });
            }
        });
    }

    window.focusFlight = function(id) {
        const marker = flightMarkers[id];
        const f = allFlights.find(fl => fl.id === id);
        if (marker && f) {
            map.setView(marker.getLatLng(), Math.max(map.getZoom(), 7), { animate: true });
            openFlightPanel(f);
        }
    };

    initMap();
    renderFlights(@json($flights));

    Livewire.on('flightsUpdated', (data) => {
        if (map) renderFlights(data.flights);
    });

    setTimeout(() => { if (map) map.invalidateSize(); }, 500);
});
</script>
</div>


