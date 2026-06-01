<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subfleet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'airline_id',
        'hub_id',
        'type',
        'simbrief_type',
        'name',
        'fuel_type',
        'cost_block_hour',
        'cost_delay_minute',
        'ground_handling_multiplier',
        'cargo_capacity',
        'fuel_capacity',
        'gross_weight',
    ];

    protected function casts(): array
    {
        return [
            'airline_id' => 'integer',
            'hub_id' => 'integer',
            'cost_block_hour' => 'decimal:2',
            'cost_delay_minute' => 'decimal:2',
            'ground_handling_multiplier' => 'decimal:2',
            'cargo_capacity' => 'decimal:2',
            'fuel_capacity' => 'decimal:2',
            'gross_weight' => 'decimal:2',
        ];
    }

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function aircraft(): HasMany
    {
        return $this->hasMany(Aircraft::class);
    }

    public function fares(): BelongsToMany
    {
        return $this->belongsToMany(Fare::class, 'subfleet_fare')
                    ->withPivot('price', 'cost', 'capacity')
                    ->withTimestamps();
    }

    public function typeratings(): BelongsToMany
    {
        return $this->belongsToMany(Typerating::class, 'typerating_subfleet')
                    ->withTimestamps();
    }
}
