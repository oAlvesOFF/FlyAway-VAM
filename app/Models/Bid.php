<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Bid",
    description: "Bid model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "user_id", type: "integer", example: 1),
        new OA\Property(property: "schedule_id", type: "integer", example: 1),
        new OA\Property(property: "aircraft_id", type: "integer", example: 1),
        new OA\Property(property: "simbrief_ofp", type: "object", example: []),
        new OA\Property(property: "simbrief_xml", type: "string", example: ""),
    ]
)]
class Bid extends Model
{
    protected $fillable = [
        'user_id',
        'schedule_id',
        'aircraft_id',
        'simbrief_ofp',
        'simbrief_xml',
    ];

    protected function casts(): array
    {
        return [
            'simbrief_ofp' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function aircraft(): BelongsTo
    {
        return $this->belongsTo(Aircraft::class);
    }
}
