<?php

namespace Database\Factories;

use App\Models\BusinessProfile;
use App\Models\Deal;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'deal_id' => Deal::factory(),
            'business_profile_id' => BusinessProfile::factory(),
            'client_user_id' => User::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'body' => $this->faker->optional()->paragraph(),
        ];
    }
}
