<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimBriefLayout extends Model
{
    public $table = 'simbrief_layouts';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'name_long',
    ];
}
