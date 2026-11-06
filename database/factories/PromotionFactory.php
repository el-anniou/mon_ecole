<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Promotion>
 */
class PromotionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titre' => 'Promotion '.fake()->unique()->word(),
            'texte' => fake()->sentence(),
            'debut' => now()->subDays(3)->toDateString(),
            'fin' => now()->addDays(7)->toDateString(),
            'lien_action' => null,
            'publiee' => true,
        ];
    }

    public function expiree(): static
    {
        return $this->state([
            'debut' => now()->subDays(30)->toDateString(),
            'fin' => now()->subDays(2)->toDateString(),
        ]);
    }

    public function aVenir(): static
    {
        return $this->state([
            'debut' => now()->addDays(5)->toDateString(),
            'fin' => now()->addDays(15)->toDateString(),
        ]);
    }

    public function depubliee(): static
    {
        return $this->state(['publiee' => false]);
    }
}
