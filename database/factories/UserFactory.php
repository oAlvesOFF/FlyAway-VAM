<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'pilot_id' => 'ASR' . str_pad(fake()->unique()->numberBetween(2001, 9999), 4, '0', STR_PAD_LEFT),
            'total_hours' => fake()->randomFloat(1, 0, 500),
            'total_flights' => fake()->numberBetween(0, 200),
            'last_location' => fake()->randomElement(['YSSY', 'YMML', 'YBBN', 'YPPH', 'YBCS', 'NZAA', 'WSSS', 'RJTT', 'EGLL']),
            'status' => 'active',
            'is_admin' => false,
            'role_id' => null,
            'simbrief_username' => fake()->optional()->userName(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
