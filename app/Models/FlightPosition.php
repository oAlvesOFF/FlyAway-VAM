<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlightPosition extends Model
{
    protected $fillable = [
        'active_flight_id',
        'flight_number',
        'latitude',
        'longitude',
        'heading',
        'altitude',
        'ground_speed',
        'phase',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:6',
            'longitude' => 'decimal:6',
            'recorded_at' => 'datetime',
        ];
    }

    public function activeFlight(): BelongsTo
    {
        return $this->belongsTo(ActiveFlight::class);
    }
}
