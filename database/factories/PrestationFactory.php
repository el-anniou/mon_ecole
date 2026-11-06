<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Prestation>
 */
class PrestationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => 'Prestation '.fake()->unique()->word(),
            'description' => fake()->boolean(60) ? fake()->sentence() : null,
            'type_prix' => 'fixe',
            'prix_min' => fake()->numberBetween(10, 80),
            'prix_max' => null,
            'duree_minutes' => fake()->boolean(50) ? fake()->numberBetween(30, 180) : null,
            'position' => fake()->numberBetween(1, 50),
            'publiee' => true,
        ];
    }

    public function masquee(): static
    {
        return $this->state(['publiee' => false]);
    }

    public function surDemande(): static
    {
        return $this->state(['type_prix' => 'sur_demande', 'prix_min' => null, 'prix_max' => null]);
    }
}
