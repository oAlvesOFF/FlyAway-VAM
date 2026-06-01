<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "ActiveFlight",
    description: "ActiveFlight model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "flight_number", type: "string", example: "FVA123"),
        new OA\Property(property: "aircraft_registration", type: "string", example: "N123AB"),
        new OA\Property(property: "aircraft_icao", type: "string", example: "A320"),
        new OA\Property(property: "aircraft_type", type: "string", example: "Airbus A320neo"),
        new OA\Property(property: "departure", type: "string", example: "KJFK"),
        new OA\Property(property: "arrival", type: "string", example: "EGLL"),
        new OA\Property(property: "departure_lat", type: "number", format: "float", example: 40.6413),
        new OA\Property(property: "departure_lng", type: "number", format: "float", example: -73.7781),
        new OA\Property(property: "arrival_lat", type: "number", format: "float", example: 51.4700),
        new OA\Property(property: "arrival_lng", type: "number", format: "float", example: -0.4543),
        new OA\Property(property: "current_lat", type: "number", format: "float", example: 45.0),
        new OA\Property(property: "current_lng", type: "number", format: "float", example: -20.0),
        new OA\Property(property: "heading", type: "integer", example: 90),
        new OA\Property(property: "altitude", type: "integer", example: 35000),
        new OA\Property(property: "ground_speed", type: "integer", example: 450),
        new OA\Property(property: "phase", type: "string", example: "enroute"),
        new OA\Property(property: "status", type: "string", example: "active"),
        new OA\Property(property: "user_id", type: "integer", example: 1),
        new OA\Property(property: "started_at", type: "string", format: "date-time", example: "2024-01-01T12:00:00Z"),
        new OA\Property(property: "position_updated_at", type: "string", format: "date-time", example: "2024-01-01T12:05:00Z"),
        new OA\Property(property: "ended_at", type: "string", format: "date-time", example: null),
    ]
)]
class ActiveFlight extends Model
{
    protected $fillable = [
        'flight_number',
        'aircraft_registration',
        'aircraft_icao',
        'aircraft_type',
        'departure',
        'arrival',
        'departure_lat',
        'departure_lng',
        'arrival_lat',
        'arrival_lng',
        'current_lat',
        'current_lng',
        'heading',
        'altitude',
        'ground_speed',
        'phase',
        'status',
        'user_id',
        'airline_id',
        'started_at',
        'position_updated_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'departure_lat' => 'decimal:6',
            'departure_lng' => 'decimal:6',
            'arrival_lat' => 'decimal:6',
            'arrival_lng' => 'decimal:6',
            'current_lat' => 'decimal:6',
            'current_lng' => 'decimal:6',
            'started_at' => 'datetime',
            'position_updated_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(FlightPosition::class);
    }
}
