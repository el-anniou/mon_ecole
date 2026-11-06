<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Temoignage>
 */
class TemoignageFactory extends Factory
{
    /**
     * Par défaut : témoignage reçu, SANS autorisation ni publication
     * (l'état le plus restrictif, conforme au verrou CDC §8).
     */
    public function definition(): array
    {
        return [
            'auteur' => fake()->firstName('female'),
            'contenu' => fake()->sentences(3, true),
            'autorisation_affichage' => false,
            'autorisation_notee_le' => null,
            'autorisation_note' => null,
            'publie' => false,
        ];
    }

    /** Autorisation enregistrée, mais pas encore publié. */
    public function autorise(): static
    {
        return $this->state(fn () => [
            'autorisation_affichage' => true,
            'autorisation_notee_le' => now()->subDays(fake()->numberBetween(1, 30))->toDateString(),
            'autorisation_note' => 'Accord par WhatsApp',
        ]);
    }

    /** Autorisé ET publié : visible sur le site public. */
    public function publie(): static
    {
        return $this->autorise()->state(fn () => ['publie' => true]);
    }
}
