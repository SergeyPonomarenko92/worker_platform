<?php

namespace Database\Factories;

use App\Models\BusinessProfile;
use App\Models\PortfolioPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PortfolioPost>
 */
class PortfolioPostFactory extends Factory
{
    protected $model = PortfolioPost::class;

    public function definition(): array
    {
        return [
            'business_profile_id' => BusinessProfile::factory(),
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->paragraphs(3, true),
            'published_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'published_at' => null,
        ]);
    }
}
