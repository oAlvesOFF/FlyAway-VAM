<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Role",
    description: "Role model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "Pilot"),
        new OA\Property(property: "slug", type: "string", example: "pilot"),
        new OA\Property(property: "description", type: "string", example: "Standard pilot role"),
        new OA\Property(property: "is_staff", type: "boolean", example: false),
    ]
)]
class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_staff'];

    protected function casts(): array
    {
        return ['is_staff' => 'boolean'];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function hasPermission(string $slug): bool
    {
        return $this->permissions()->where('slug', $slug)->exists();
    }
}
