<?php

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Netflix', 'Spotify', 'Sală', 'YouTube Premium', 'iCloud', 'Disney+']),
            'monthly_cost' => fake()->randomFloat(2, 1, 100),
            'status' => SubscriptionStatus::Active,
        ];
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Paused,
        ]);
    }
}
