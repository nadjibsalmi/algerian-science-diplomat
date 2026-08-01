<?php

namespace Database\Factories;

use App\Modules\Embassies\Models\Embassy;
use App\Modules\Offers\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Offer> */
class OfferFactory extends Factory
{
    protected $model = Offer::class;

    public function definition(): array
    {
        $title = $this->faker->catchPhrase();

        return [
            'embassy_id' => Embassy::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'description' => $this->faker->paragraphs(3, true),
            'country' => $this->faker->country(),
            'city' => $this->faker->city(),
            'offer_type' => $this->faker->randomElement(['internship', 'scholarship', 'research', 'phd', 'postdoc']),
            'level' => $this->faker->randomElement(['bachelor', 'master', 'phd']),
            'deadline' => $this->faker->dateTimeBetween('+1 month', '+6 months'),
            'status' => 'draft',
            'visibility' => 'public',
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
