<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BusinessProfile>
 */
class BusinessProfileFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'about' => $this->faker->optional()->paragraph(),
            'country_code' => 'UA',
            'city' => $this->faker->randomElement(['Київ', 'Львів', 'Одеса', 'Харків', 'Дніпро']),
            'address' => $this->faker->optional()->streetAddress(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'website' => $this->faker->optional()->url(),
            'is_active' => true,
        ];
    }
}
