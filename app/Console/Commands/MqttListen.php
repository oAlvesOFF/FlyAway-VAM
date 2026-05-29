<?php

namespace App\Console\Commands;

use App\Models\ActiveFlight;
use App\Models\FlightPosition;
use App\Services\MqttService;
use Illuminate\Console\Command;

class MqttListen extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Subscribe to MQTT topics for real-time ACARS flight tracking';

    public function handle(MqttService $mqtt): void
    {
        $mqtt = app(MqttService::class);

        if (!$mqtt->isEnabled()) {
            $this->warn('MQTT is not enabled. Set MQTT_ENABLED=true and MQTT_HOST in .env');
            return;
        }

        if (!$mqtt->connect()) {
            $this->error('Failed to connect to MQTT broker at ' . env('MQTT_HOST', '127.0.0.1'));
            return;
        }

        $this->info('Connected to MQTT broker. Listening for flight tracking data...');

        $mqtt->subscribe('flyaway/flights/+/track', function (string $topic, array $data) {
            $flightNumber = $data['flight_number'] ?? null;
            if (!$flightNumber) return;

            $flight = ActiveFlight::where('flight_number', $flightNumber)
                ->where('status', 'active')
                ->first();

            if ($flight) {
                $flight->update([
                    'current_lat' => $data['current_lat'] ?? $flight->current_lat,
                    'current_lng' => $data['current_lng'] ?? $flight->current_lng,
                    'heading' => $data['heading'] ?? $flight->heading,
                    'altitude' => $data['altitude'] ?? $flight->altitude,
                    'ground_speed' => $data['ground_speed'] ?? $flight->ground_speed,
                    'phase' => $data['phase'] ?? $flight->phase,
                    'position_updated_at' => now(),
                ]);
                $this->line("  Updated {$flightNumber}: {$data['current_lat']},{$data['current_lng']} @ {$data['altitude']}ft");
            } else {
                $flight = ActiveFlight::create([
                    'flight_number' => $data['flight_number'],
                    'aircraft_registration' => $data['aircraft_registration'] ?? 'N/A',
                    'aircraft_icao' => $data['aircraft_icao'] ?? 'N/A',
                    'aircraft_type' => $data['aircraft_type'] ?? 'N/A',
                    'departure' => $data['departure'] ?? 'N/A',
                    'arrival' => $data['arrival'] ?? 'N/A',
                    'departure_lat' => $data['departure_lat'] ?? null,
                    'departure_lng' => $data['departure_lng'] ?? null,
                    'arrival_lat' => $data['arrival_lat'] ?? null,
                    'arrival_lng' => $data['arrival_lng'] ?? null,
                    'current_lat' => $data['current_lat'] ?? 0,
                    'current_lng' => $data['current_lng'] ?? 0,
                    'heading' => $data['heading'] ?? 0,
                    'altitude' => $data['altitude'] ?? 0,
                    'ground_speed' => $data['ground_speed'] ?? 0,
                    'phase' => $data['phase'] ?? 'enroute',
                    'status' => 'active',
                    'started_at' => now(),
                    'position_updated_at' => now(),
                ]);
                $this->line("  Created {$flightNumber}");
            }

            // Save position history
            FlightPosition::create([
                'active_flight_id' => $flight->id,
                'flight_number' => $flight->flight_number,
                'latitude' => $data['current_lat'] ?? $flight->current_lat,
                'longitude' => $data['current_lng'] ?? $flight->current_lng,
                'heading' => $data['heading'] ?? $flight->heading,
                'altitude' => $data['altitude'] ?? $flight->altitude,
                'ground_speed' => $data['ground_speed'] ?? $flight->ground_speed,
                'phase' => $data['phase'] ?? $flight->phase,
                'recorded_at' => now(),
            ]);
        });

        $mqtt->subscribe('flyaway/flights/+/complete', function (string $topic, array $data) {
            $flightNumber = $data['flight_number'] ?? null;
            if (!$flightNumber) return;

            $flight = ActiveFlight::where('flight_number', $flightNumber)
                ->where('status', 'active')
                ->first();

            if ($flight) {
                $flight->update([
                    'status' => 'completed',
                    'phase' => 'landed',
                    'position_updated_at' => now(),
                    'ended_at' => now(),
                ]);
                $this->line("  Completed {$flightNumber}");
            }
        });

        $mqtt->loop(true);
    }
}
