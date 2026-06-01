<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Schedule",
    description: "Schedule model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "flight_number", type: "string", example: "FVA123"),
        new OA\Property(property: "departure", type: "string", example: "KJFK"),
        new OA\Property(property: "arrival", type: "string", example: "EGLL"),
        new OA\Property(property: "route", type: "string", example: "KJFK-KLAX"),
        new OA\Property(property: "aircraft_type", type: "string", example: "A320"),
        new OA\Property(property: "flight_time", type: "number", format: "float", example: 5.5),
        new OA\Property(property: "departure_time", type: "string", format: "date-time", example: "2024-01-01T12:00:00Z"),
        new OA\Property(property: "altitude", type: "integer", example: 35000),
    ]
)]
class Schedule extends Model
{
    protected $fillable = [
        'flight_number',
        'departure',
        'arrival',
        'route',
        'aircraft_type',
        'flight_time',
        'departure_time',
        'altitude',
        'airline_id',
    ];

    public function airline(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }
}
