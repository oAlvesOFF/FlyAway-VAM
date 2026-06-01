<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SimBriefAircraft extends Model
{
    public $table = 'simbrief_aircraft';

    protected $fillable = [
        'icao',
        'name',
        'details',
    ];

    public function sbairframes(): HasMany
    {
        return $this->hasMany(SimBriefAirframe::class, 'icao', 'icao');
    }
}
