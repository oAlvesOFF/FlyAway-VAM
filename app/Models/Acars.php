<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Acars extends Model
{
    use HasFactory, HasUuids;

    public $table = 'acars';
    protected $keyType = 'string';
    public $incrementing = false;

    public $fillable = [
        'pirep_id',
        'type',
        'nav_type',
        'order',
        'name',
        'status',
        'log',
        'lat',
        'lon',
        'distance',
        'heading',
        'altitude',
        'altitude_agl',
        'altitude_msl',
        'vs',
        'gs',
        'ias',
        'transponder',
        'autopilot',
        'fuel_flow',
        'sim_time',
        'source',
    ];

    public $casts = [
        'type'         => 'integer',
        'order'        => 'integer',
        'nav_type'     => 'integer',
        'lat'          => 'float',
        'lon'          => 'float',
        'distance'     => 'integer',
        'heading'      => 'integer',
        'altitude_agl' => 'float',
        'altitude_msl' => 'float',
        'vs'           => 'float',
        'gs'           => 'integer',
        'ias'          => 'integer',
        'transponder'  => 'integer',
        'fuel_flow'    => 'float',
        'sim_time'     => 'datetime',
    ];

    public function pirep(): BelongsTo
    {
        return $this->belongsTo(Pirep::class, 'pirep_id');
    }
}
