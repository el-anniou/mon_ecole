<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\RendezVous>
 */
class RendezVousFactory extends Factory
{
    /**
     * Par défaut : demande publique en attente, avec souhait souple.
     */
    public function definition(): array
    {
        return [
            'statut' => 'en_attente',
            'source' => 'site',
            'nom_contact' => fake()->firstName('female'),
            'moyen_contact' => '+261 3'.fake()->numerify('# ## ### ##'),
            'date_souhaitee' => now()->addDays(fake()->numberBetween(2, 20))->toDateString(),
            'periode_souhaitee' => fake()->randomElement(['matin', 'apres_midi', 'soir']),
            'message' => fake()->boolean(70) ? fake()->sentence(10) : null,
            'debut' => null,
            'duree_minutes' => null,
        ];
    }

    /** Rendez-vous confirmé et planifié. */
    public function confirme(): static
    {
        return $this->state(fn () => [
            'statut' => 'confirme',
            'debut' => now()->addDays(fake()->numberBetween(1, 15))->setTime(fake()->numberBetween(9, 17), 0),
            'duree_minutes' => fake()->randomElement([60, 90, 120]),
        ]);
    }

    /** Rendez-vous passé et terminé. */
    public function termine(): static
    {
        return $this->state(fn () => [
            'statut' => 'termine',
            'debut' => now()->subDays(fake()->numberBetween(2, 30))->setTime(fake()->numberBetween(9, 17), 0),
            'duree_minutes' => 60,
        ]);
    }

    /** Créé à la main par la propriétaire. */
    public function manuel(): static
    {
        return $this->state(fn () => ['source' => 'manuel']);
    }
}
