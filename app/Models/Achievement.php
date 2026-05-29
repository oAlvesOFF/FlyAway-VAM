<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Achievement",
    description: "Achievement model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "Frequent Flyer"),
        new OA\Property(property: "slug", type: "string", example: "frequent-flyer"),
        new OA\Property(property: "description", type: "string", example: "Fly 50 flights"),
        new OA\Property(property: "icon", type: "string", example: "/images/achievements/frequent_flyer.png"),
        new OA\Property(property: "category", type: "string", example: "flight"),
        new OA\Property(property: "threshold", type: "integer", example: 50),
        new OA\Property(property: "metric", type: "string", example: "total_flights"),
    ]
)]
class Achievement extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'category', 'threshold', 'metric'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }

    public function isUnlockedBy(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public static function checkAndUnlock(User $user): array
    {
        $unlocked = [];

        $achievements = static::all();
        foreach ($achievements as $achievement) {
            if ($achievement->isUnlockedBy($user)) continue;

            $progress = match ($achievement->metric) {
                'total_flights' => (int) $user->total_flights,
                'total_hours' => (int) $user->total_hours,
                'perfect_landings' => (int) $user->pireps()->where('score', 100)->count(),
                'pireps_filed' => (int) $user->pireps()->count(),
                'routes_flown' => (int) $user->pireps()->distinct('flight_number')->count('flight_number'),
                default => 0,
            };

            if ($progress >= $achievement->threshold) {
                $user->achievements()->attach($achievement->id, ['unlocked_at' => now()]);
                $unlocked[] = $achievement;
            }
        }

        return $unlocked;
    }
}
