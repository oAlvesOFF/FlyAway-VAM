<?php

use App\Models\ActiveFlight;
use App\Models\Aircraft;
use App\Models\Schedule;
use Livewire\Volt\Component;

new class extends Component {
    public $aircraft = [];
    public $schedules = [];
    public $airports = [];
    public $simulating = false;
    public $simInterval = null;

    public $flight_number = '';
    public $aircraft_registration = '';
    public $aircraft_icao = '';
    public $aircraft_type = '';
    public $departure = '';
    public $arrival = '';
    public $departure_lat = '';
    public $departure_lng = '';
    public $arrival_lat = '';
    public $arrival_lng = '';
    public $current_lat = '';
    public $current_lng = '';
    public $heading = 0;
    public $altitude = 0;
    public $ground_speed = 0;
    public $phase = 'preflight';
    public $activeFlightId = null;
    public $progress = 0;

    public $statusMessage = '';
    public $errorMessage = '';

    public $logs = [];

    public function mount(): void
    {
        $this->aircraft = Aircraft::get(['id', 'registration', 'icao', 'name', 'category'])->toArray();
        $this->schedules = Schedule::select('id', 'flight_number', 'departure', 'arrival', 'aircraft_type', 'departure_time', 'altitude')->get()->toArray();

        $this->airports = [
            'YSSY' => ['lat' => -33.9461, 'lng' => 151.1772],
            'YMML' => ['lat' => -37.6733, 'lng' => 144.8433],
            'YBBN' => ['lat' => -27.3842, 'lng' => 153.1175],
            'YPPH' => ['lat' => -31.9403, 'lng' => 115.9664],
            'YBCS' => ['lat' => -16.8858, 'lng' => 145.7553],
            'NZAA' => ['lat' => -37.0081, 'lng' => 174.7920],
            'WSSS' => ['lat' => 1.3592, 'lng' => 103.9894],
            'RJTT' => ['lat' => 35.5494, 'lng' => 139.7798],
            'EGLL' => ['lat' => 51.4775, 'lng' => -0.4614],
        ];
    }

    public function selectFlight($flightNumber): void
    {
        $schedule = collect($this->schedules)->firstWhere('flight_number', $flightNumber);
        if (!$schedule) return;

        $this->flight_number = $schedule['flight_number'];
        $this->departure = $schedule['departure'];
        $this->arrival = $schedule['arrival'];
        $this->aircraft_type = $schedule['aircraft_type'];

        $depAirport = $this->airports[$schedule['departure']] ?? null;
        $arrAirport = $this->airports[$schedule['arrival']] ?? null;
        if ($depAirport) {
            $this->departure_lat = $depAirport['lat'];
            $this->departure_lng = $depAirport['lng'];
            $this->current_lat = $depAirport['lat'];
            $this->current_lng = $depAirport['lng'];
        }
        if ($arrAirport) {
            $this->arrival_lat = $arrAirport['lat'];
            $this->arrival_lng = $arrAirport['lng'];
        }

        $this->selectAircraftType($schedule['aircraft_type']);
    }

    public function selectAircraftType($type): void
    {
        $ac = collect($this->aircraft)->firstWhere('category', $type);
        if ($ac) {
            $this->aircraft_registration = $ac['registration'];
            $this->aircraft_icao = $ac['icao'];
        }
    }

    public function startSimulation(): void
    {
        if (!$this->flight_number || !$this->departure || !$this->arrival) {
            $this->errorMessage = 'Please select a flight first.';
            return;
        }

        $this->simulating = true;
        $this->progress = 0;
        $this->phase = 'boarding';
        $this->altitude = 0;
        $this->ground_speed = 0;

        $this->addLog('Simulation started — boarding at ' . $this->departure);

        $this->sendPosition();
    }

    public function stopSimulation(): void
    {
        $this->simulating = false;
        $this->progress = 0;
        $this->phase = 'preflight';
        $this->addLog('Simulation stopped.');

        if ($this->activeFlightId) {
            try {
                $flight = ActiveFlight::find($this->activeFlightId);
                if ($flight) {
                    $flight->update(['status' => 'completed', 'phase' => 'landed']);
                }
            } catch (\Exception $e) {}
            $this->activeFlightId = null;
        }
    }

    public function advanceSimulation(): void
    {
        if (!$this->simulating) return;

        $this->progress = min(1, $this->progress + 0.02);

        if ($this->progress < 0.1) {
            $this->phase = 'departed';
            $this->altitude = min(5000, $this->altitude + 500);
            $this->ground_speed = min(250, $this->ground_speed + 30);
        } elseif ($this->progress < 0.15) {
            $this->phase = 'departed';
            $this->altitude = min(15000, $this->altitude + 1000);
            $this->ground_speed = min(320, $this->ground_speed + 20);
        } elseif ($this->progress < 0.85) {
            $this->phase = 'enroute';
            $targetAlt = rand(28000, 40000);
            $this->altitude = $this->altitude + ($targetAlt - $this->altitude) * 0.05;
            $this->ground_speed = 430 + rand(0, 60);
        } elseif ($this->progress < 0.95) {
            $this->phase = 'onapproach';
            $this->altitude = max(2000, $this->altitude - 500);
            $this->ground_speed = max(160, $this->ground_speed - 15);
        } else {
            $this->phase = 'onapproach';
            $this->altitude = max(500, $this->altitude - 200);
            $this->ground_speed = max(140, $this->ground_speed - 10);
        }

        if ($this->progress >= 1) {
            $this->phase = 'landed';
            $this->altitude = 0;
            $this->ground_speed = 0;
            $this->current_lat = (float) $this->arrival_lat;
            $this->current_lng = (float) $this->arrival_lng;
            $this->simulating = false;
            $this->addLog('Flight completed — landed at ' . $this->arrival);

            if ($this->activeFlightId) {
                try {
                    $flight = ActiveFlight::find($this->activeFlightId);
                    if ($flight) {
                        $flight->update(['status' => 'completed', 'phase' => 'landed']);
                    }
                } catch (\Exception $e) {}
                $this->activeFlightId = null;
            }

            $this->sendPosition();
            return;
        }

        $depLat = (float) $this->departure_lat;
        $depLng = (float) $this->departure_lng;
        $arrLat = (float) $this->arrival_lat;
        $arrLng = (float) $this->arrival_lng;

        $lat = $depLat + ($arrLat - $depLat) * $this->progress;
        $lng = $depLng + ($arrLng - $depLng) * $this->progress;

        $heading = round(rad2deg(atan2($arrLng - $depLng, $arrLat - $depLat)));

        $this->current_lat = round($lat + (rand(-100, 100) / 10000), 6);
        $this->current_lng = round($lng + (rand(-100, 100) / 10000), 6);
        $this->heading = ($heading + 360) % 360;

        $this->sendPosition();
    }

    public function sendPosition(): void
    {
        try {
            $flight = ActiveFlight::updateOrCreate(
                ['flight_number' => $this->flight_number, 'status' => 'active'],
                [
                    'aircraft_registration' => $this->aircraft_registration,
                    'aircraft_icao' => $this->aircraft_icao,
                    'aircraft_type' => $this->aircraft_type,
                    'departure' => $this->departure,
                    'arrival' => $this->arrival,
                    'departure_lat' => $this->departure_lat ?: null,
                    'departure_lng' => $this->departure_lng ?: null,
                    'arrival_lat' => $this->arrival_lat ?: null,
                    'arrival_lng' => $this->arrival_lng ?: null,
                    'current_lat' => $this->current_lat,
                    'current_lng' => $this->current_lng,
                    'heading' => $this->heading,
                    'altitude' => (int) $this->altitude,
                    'ground_speed' => (int) $this->ground_speed,
                    'phase' => $this->phase,
                    'started_at' => $this->activeFlightId ? null : now(),
                    'position_updated_at' => now(),
                ]
            );
            $this->activeFlightId = $flight->id;

            // Save position history
            \App\Models\FlightPosition::create([
                'active_flight_id' => $flight->id,
                'flight_number' => $flight->flight_number,
                'latitude' => $this->current_lat,
                'longitude' => $this->current_lng,
                'heading' => $this->heading,
                'altitude' => (int) $this->altitude,
                'ground_speed' => (int) $this->ground_speed,
                'phase' => $this->phase,
                'recorded_at' => now(),
            ]);

            $this->addLog("Position sent — {$this->phase} @ FL{$this->altitude} / {$this->ground_speed}kts / {$this->heading}°");
        } catch (\Exception $e) {
            $this->addLog("Error sending position: " . $e->getMessage());
        }
    }

    public function addLog($message): void
    {
        array_unshift($this->logs, '[' . now()->format('H:i:s') . '] ' . $message);
        if (count($this->logs) > 50) {
            array_pop($this->logs);
        }
    }

    public function clearLogs(): void
    {
        $this->logs = [];
    }
}; ?>

<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">ACARS Client</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Browser-based flight tracking simulator. Send real-time position data to the Live Map.</p>
        </div>
        <a href="{{ route('live-map') }}" wire:navigate class="text-sm text-crimson-600 dark:text-crimson-400 hover:underline">View Live Map →</a>
    </div>

    @if($errorMessage)
        <div class="card bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 p-4 text-red-700 dark:text-red-400 text-sm">
            {{ $errorMessage }}
        </div>
    @endif

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Controls --}}
        <div class="space-y-4">
            <div class="card p-5 space-y-4">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Flight Setup</h2>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Select Schedule</label>
                    <select wire:model.change="flight_number" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                        <option value="">— Select a flight —</option>
                        @foreach($schedules as $s)
                            <option value="{{ $s['flight_number'] }}">{{ $s['flight_number'] }} — {{ $s['departure'] }}→{{ $s['arrival'] }} ({{ $s['aircraft_type'] }})</option>
                        @endforeach
                    </select>
                </div>

                @if($flight_number)
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><span class="text-xs text-slate-400">Route</span><p class="font-medium text-slate-900 dark:text-white">{{ $departure }} → {{ $arrival }}</p></div>
                    <div><span class="text-xs text-slate-400">Aircraft</span><p class="font-medium text-slate-900 dark:text-white">{{ $aircraft_registration }} ({{ $aircraft_icao }})</p></div>
                </div>
                @endif

                @if(!$simulating)
                    <button wire:click="startSimulation" class="btn-primary w-full text-sm py-2.5">
                        <svg class="w-4 h-4 inline mr-1.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        Start Simulation
                    </button>
                @else
                    <button wire:click="advanceSimulation" class="btn-primary w-full text-sm py-2.5 mb-2">
                        Advance 2% Progress
                    </button>
                    <button wire:click="stopSimulation" class="w-full px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-medium transition-colors">
                        <svg class="w-4 h-4 inline mr-1.5" fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12"/></svg>
                        Stop Simulation
                    </button>
                @endif
            </div>

            {{-- Telemetry --}}
            <div class="card p-5 space-y-3">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Live Telemetry</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3">
                        <p class="text-xs text-slate-400">Phase</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white capitalize">{{ $phase }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3">
                        <p class="text-xs text-slate-400">Progress</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ round($progress * 100) }}%</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3">
                        <p class="text-xs text-slate-400">Altitude</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white font-mono">{{ number_format((int)$altitude) }} ft</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3">
                        <p class="text-xs text-slate-400">Ground Speed</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white font-mono">{{ (int)$ground_speed }} kts</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3">
                        <p class="text-xs text-slate-400">Heading</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white font-mono">{{ $heading }}°</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3">
                        <p class="text-xs text-slate-400">Position</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white font-mono text-[11px]">{{ $current_lat }}, {{ $current_lng }}</p>
                    </div>
                </div>
                @if($simulating)
                    <div class="h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-crimson-500 rounded-full transition-all duration-500" style="width: {{ $progress * 100 }}%"></div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Log --}}
        <div class="card p-5 flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">ACARS Log</h2>
                <button wire:click="clearLogs" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">Clear</button>
            </div>
            <div class="flex-1 bg-slate-950 dark:bg-black rounded-xl p-3 font-mono text-xs leading-relaxed max-h-[500px] overflow-y-auto" id="acars-log">
                @if(count($logs) === 0)
                    <span class="text-slate-600">// ACARS client ready. Select a flight and start simulation.</span>
                @endif
                @foreach($logs as $log)
                    <div class="text-green-400">{{ $log }}</div>
                @endforeach
            </div>
        </div>
    </div>
</div>
