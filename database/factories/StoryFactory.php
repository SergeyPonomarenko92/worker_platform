<?php

namespace Database\Factories;

use App\Models\BusinessProfile;
use App\Models\Story;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Story>
 */
class StoryFactory extends Factory
{
    protected $model = Story::class;

    public function definition(): array
    {
        return [
            'business_profile_id' => BusinessProfile::factory(),
            'media_path' => 'demo/story-'.$this->faker->uuid.'.jpg',
            'caption' => $this->faker->optional()->sentence(),
            'expires_at' => now()->addHours(24),
        ];
    }
}
