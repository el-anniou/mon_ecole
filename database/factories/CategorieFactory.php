<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Categorie>
 */
class CategorieFactory extends Factory
{
    public function definition(): array
    {
        $nom = 'Catégorie '.fake()->unique()->word();

        return [
            'nom' => $nom,
            'slug' => Str::slug($nom),
            'position' => fake()->numberBetween(1, 50),
            'visible' => true,
        ];
    }

    public function masquee(): static
    {
        return $this->state(['visible' => false]);
    }
}
