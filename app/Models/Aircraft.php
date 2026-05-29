<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Aircraft",
    description: "Aircraft model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "registration", type: "string", example: "N123AB"),
        new OA\Property(property: "icao", type: "string", example: "A320"),
        new OA\Property(property: "name", type: "string", example: "Airbus A320neo"),
        new OA\Property(property: "location", type: "string", example: "KJFK"),
        new OA\Property(property: "status", type: "string", example: "available"),
        new OA\Property(property: "category", type: "string", example: "narrow-body"),
        new OA\Property(property: "last_service_at", type: "string", format: "date-time", example: "2024-01-01T00:00:00Z"),
        new OA\Property(property: "total_hours_since_service", type: "number", format: "float", example: 150.5),
    ]
)]
class Aircraft extends Model
{
    protected $fillable = [
        'registration',
        'icao',
        'name',
        'location',
        'status',
        'category',
        'last_service_at',
        'total_hours_since_service',
    ];

    protected function casts(): array
    {
        return [
            'last_service_at' => 'datetime',
            'total_hours_since_service' => 'decimal:2',
        ];
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }
}
