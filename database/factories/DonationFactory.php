<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Donation>
 */
class DonationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'user_id' => User::inRandomOrder()->first()?->id,
            'campaign_id' => Campaign::inRandomOrder()->first()?->id,
            'contribution_amount' =>fake()->numberBetween(100, 100000),
            'contribution_details' =>fake()->sentence(),
            'currency_type' =>fake()->randomElement(['USD','EUR','SYP']),
            'pledge_to_donate' =>fake()->boolean(),
            'donate_directly' => fake()->boolean(),
            'status' =>fake()->randomElement(['متوافق', 'غير متوافق', 'قيد التدقيق']),
            'image' => 'donations/images/test.jp',
            'pending' =>fake()->boolean(),
        ];
    }
}
