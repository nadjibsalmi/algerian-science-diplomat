<?php

namespace Database\Factories;

use App\Modules\Embassies\Models\Embassy;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Embassy> */
class EmbassyFactory extends Factory
{
    protected $model = Embassy::class;

    public function definition(): array
    {
        return [
            'country' => $this->faker->country(),
            'official_name' => 'Embassy of '.$this->faker->country(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'website' => $this->faker->url(),
            'address' => $this->faker->address(),
            'verified' => true,
            'status' => 'active',
        ];
    }
}
