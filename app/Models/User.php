<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "User",
    description: "User model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "John Doe"),
        new OA\Property(property: "email", type: "string", example: "john@example.com"),
        new OA\Property(property: "pilot_id", type: "string", example: "ASR0001"),
        new OA\Property(property: "rank_id", type: "integer", example: 1),
        new OA\Property(property: "role_id", type: "integer", example: 1),
        new OA\Property(property: "total_hours", type: "number", format: "float", example: 123.45),
        new OA\Property(property: "total_flights", type: "integer", example: 50),
        new OA\Property(property: "last_location", type: "string", example: "KJFK"),
        new OA\Property(property: "status", type: "string", example: "active"),
        new OA\Property(property: "is_admin", type: "boolean", example: false),
        new OA\Property(property: "simbrief_username", type: "string", example: "johndoe"),
        new OA\Property(property: "avatar", type: "string", example: "/storage/avatars/avatar.jpg"),
        new OA\Property(property: "api_key", type: "string", example: "fly-abcdef1234567890"),
        new OA\Property(property: "suspension_reason", type: "string", example: null),
    ]
)]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'pilot_id',
        'rank_id',
        'role_id',
        'total_hours',
        'total_flights',
        'last_location',
        'status',
        'is_admin',
        'simbrief_username',
        'avatar',
        'api_key',
        'suspension_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_key',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'total_hours' => 'decimal:2',
        ];
    }

    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function pireps(): HasMany
    {
        return $this->hasMany(Pirep::class);
    }

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class)->withPivot('unlocked_at')->withTimestamps();
    }

    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class)->withPivot('progress', 'completed', 'completed_at')->withTimestamps();
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->is_admin) return true;
        return $this->role?->hasPermission($slug) ?? false;
    }

    public function isStaff(): bool
    {
        return $this->is_admin || ($this->role?->is_staff ?? false);
    }
}
