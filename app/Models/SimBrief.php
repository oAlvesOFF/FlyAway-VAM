<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimBrief extends Model
{
    protected $table = 'simbriefs';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'flight_id',
        'pirep_id',
        'acars_xml',
        'ofp_xml',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pirep(): BelongsTo
    {
        return $this->belongsTo(Pirep::class);
    }
}
