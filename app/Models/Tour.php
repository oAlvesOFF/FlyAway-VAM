<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Tour",
    description: "Tour model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "European Tour"),
        new OA\Property(property: "slug", type: "string", example: "european-tour"),
        new OA\Property(property: "description", type: "string", example: "Visit key European airports."),
        new OA\Property(property: "category", type: "string", example: "regional"),
        new OA\Property(property: "waypoints", type: "array", items: new OA\Items(type: "string"), example: ["EGLL", "LFPG", "EDDF"]),
        new OA\Property(property: "order", type: "integer", example: 1),
        new OA\Property(property: "is_active", type: "boolean", example: true),
    ]
)]
class Tour extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'category', 'waypoints', 'order', 'is_active'];

    protected function casts(): array
    {
        return [
            'waypoints' => 'array',
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('progress', 'completed', 'completed_at')
            ->withTimestamps();
    }
}
