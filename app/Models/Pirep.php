<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Pirep",
    description: "Pirep model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "user_id", type: "integer", example: 1),
        new OA\Property(property: "flight_number", type: "string", example: "FVA123"),
        new OA\Property(property: "departure", type: "string", example: "KJFK"),
        new OA\Property(property: "arrival", type: "string", example: "EGLL"),
        new OA\Property(property: "aircraft_registration", type: "string", example: "N123AB"),
        new OA\Property(property: "aircraft_icao", type: "string", example: "A320"),
        new OA\Property(property: "flight_time", type: "number", format: "float", example: 5.5),
        new OA\Property(property: "landing_rate", type: "integer", example: 150),
        new OA\Property(property: "score", type: "integer", example: 100),
        new OA\Property(property: "route", type: "string", example: "KJFK-KLAX"),
        new OA\Property(property: "status", type: "string", example: "approved"),
        new OA\Property(property: "log", type: "string", example: "Smooth landing."),
        new OA\Property(property: "submitted_at", type: "string", format: "date-time", example: "2024-01-01T12:00:00Z"),
        new OA\Property(property: "rejection_reason", type: "string", example: null),
    ]
)]
class Pirep extends Model
{
    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'flight_number',
        'departure',
        'arrival',
        'aircraft_registration',
        'aircraft_icao',
        'flight_time',
        'landing_rate',
        'score',
        'route',
        'status',
        'log',
        'submitted_at',
        'rejection_reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'flight_number', 'flight_number');
    }
}
