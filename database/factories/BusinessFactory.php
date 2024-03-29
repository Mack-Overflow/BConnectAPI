<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use \App\Models\Business;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Business>
 */
class BusinessFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Business::class;

    protected $package_tiers = ['Basic', 'Standard', 'Premium', 'Executive', 'Platinum'];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'business_name' => $this->faker->company,
            'package_tier' => $this->package_tiers[array_rand($this->package_tiers)],
            'google_place_id' => 'n/a',
            'google_review_count_onboarding' => rand(100,200), // Total Google reviews when company registered
            'total_google_review_count' => rand(200, 1000)// Total Google review count now
        ];
    }
}
