<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AccessToken;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccessToken>
 */
class AccessTokenFactory extends Factory
{
    protected $model = AccessToken::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'token' => hash('sha256', str_pad((string) fake()->unique()->numberBetween(0, 9999), 4, '0', STR_PAD_LEFT)),
            'last_used_at' => null,
            'revoked_at' => null,
        ];
    }

    public function revoked(): static
    {
        return $this->state(['revoked_at' => now()]);
    }
}
