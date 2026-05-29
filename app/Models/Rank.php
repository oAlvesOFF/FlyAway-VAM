<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Rank",
    description: "Rank model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "Captain"),
        new OA\Property(property: "minimum_hours", type: "number", format: "float", example: 100.0),
        new OA\Property(property: "image", type: "string", example: "/images/ranks/captain.png"),
        new OA\Property(property: "allowed_categories", type: "string", example: "narrow-body,wide-body"),
    ]
)]
class Rank extends Model
{
    protected $fillable = [
        'name',
        'minimum_hours',
        'image',
        'allowed_categories',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
