<?php

namespace Database\Factories;

use App\Models\BusinessProfile;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Offer>
 */
class OfferFactory extends Factory
{
    public function definition(): array
    {
        $type = $this->faker->randomElement(['service', 'product']);

        return [
            'business_profile_id' => BusinessProfile::factory(),
            'category_id' => Category::factory(),
            'type' => $type,
            'title' => $type === 'service'
                ? $this->faker->randomElement(['Електрик', 'Сантехнік', 'Ремонт квартири', 'Монтаж розеток'])
                : $this->faker->randomElement(['Хліб', 'Кава', 'Смартфон', 'Фарба']),
            'description' => $this->faker->optional()->sentence(12),
            'price_from' => $this->faker->optional()->numberBetween(100, 5000),
            'price_to' => null,
            'currency' => 'UAH',
            'is_active' => true,
        ];
    }
}
