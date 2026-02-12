<?php

namespace Database\Factories;

use App\Models\BusinessProfile;
use App\Models\Deal;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deal>
 */
class DealFactory extends Factory
{
    protected $model = Deal::class;

    public function definition(): array
    {
        return [
            'client_user_id' => User::factory(),
            'business_profile_id' => BusinessProfile::factory(),
            'offer_id' => null,
            'status' => 'draft',
            'agreed_price' => $this->faker->optional()->numberBetween(100, 2000),
            'currency' => 'UAH',
            'completed_at' => null,
        ];
    }
}
