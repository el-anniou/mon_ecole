<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Realisation>
 */
class RealisationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titre' => fake()->boolean(70) ? ucfirst(fake()->words(2, true)) : null,
            'description' => fake()->boolean(50) ? fake()->sentence() : null,
            'texte_alternatif' => null,
            'image_dossier' => 'realisations/tests/'.fake()->uuid(),
            'publiee' => true,
            'position' => fake()->numberBetween(1, 100),
            'realisee_le' => fake()->boolean(50) ? fake()->date() : null,
        ];
    }

    public function brouillon(): static
    {
        return $this->state(['publiee' => false]);
    }
}
