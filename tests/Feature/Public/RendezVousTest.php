<?php

namespace Tests\Feature\Public;

use App\Models\Prestation;
use App\Models\Realisation;
use App\Models\RendezVous;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RendezVousTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_formulaire_de_demande_est_accessible_sans_compte(): void
    {
        $this->get(route('public.rdv.create'))
            ->assertOk()
            ->assertSee('Demande de rendez-vous')
            ->assertSee('Aucune confirmation automatique');
    }

    public function test_une_demande_valide_cree_un_rendez_vous_en_attente(): void
    {
        $this->post(route('public.rdv.store'), [
            'nom_contact' => 'Amina',
            'moyen_contact' => '+261 34 00 000 00',
            'date_souhaitee' => now()->addDays(5)->toDateString(),
            'periode_souhaitee' => 'matin',
            'message' => 'Pour un mariage.',
        ])->assertRedirect(route('public.rdv.create'))
            ->assertSessionHas('demande_envoyee');

        $this->assertDatabaseHas('rendez_vous', [
            'nom_contact' => 'Amina',
            'statut' => 'en_attente',
            'source' => 'site',
            'periode_souhaitee' => 'matin',
            'debut' => null,
        ]);
    }

    public function test_le_message_de_confirmation_ne_vaut_pas_confirmation_du_rdv(): void
    {
        $this->post(route('public.rdv.store'), [
            'nom_contact' => 'Amina',
            'moyen_contact' => '034 00 000 00',
        ]);

        $this->get(route('public.rdv.create'))
            ->assertSee('ne vaut pas confirmation');
    }

    public function test_la_date_souhaitee_ne_peut_pas_etre_passee(): void
    {
        $this->from(route('public.rdv.create'))
            ->post(route('public.rdv.store'), [
                'nom_contact' => 'Amina',
                'moyen_contact' => '034 00 000 00',
                'date_souhaitee' => now()->subDay()->toDateString(),
            ])->assertSessionHasErrors('date_souhaitee');

        $this->assertDatabaseCount('rendez_vous', 0);
    }

    public function test_nom_et_contact_sont_obligatoires(): void
    {
        $this->post(route('public.rdv.store'), [])
            ->assertSessionHasErrors(['nom_contact', 'moyen_contact']);
    }

    public function test_le_piege_a_robots_ignore_la_demande_sans_le_dire(): void
    {
        $this->post(route('public.rdv.store'), [
            'nom_contact' => 'Robot',
            'moyen_contact' => 'spam@exemple.test',
            'site_web' => 'https://spam.exemple',
        ])->assertRedirect(route('public.rdv.create'))
            ->assertSessionHas('demande_envoyee');

        $this->assertDatabaseCount('rendez_vous', 0);
    }

    public function test_une_prestation_publiee_peut_etre_jointe_a_la_demande(): void
    {
        $prestation = Prestation::factory()->create();

        $this->post(route('public.rdv.store'), [
            'nom_contact' => 'Amina',
            'moyen_contact' => '034 00 000 00',
            'prestation_id' => $prestation->id,
        ]);

        $this->assertDatabaseHas('rendez_vous', [
            'nom_contact' => 'Amina',
            'prestation_id' => $prestation->id,
        ]);
    }

    public function test_une_prestation_masquee_est_refusee(): void
    {
        $prestation = Prestation::factory()->masquee()->create();

        $this->post(route('public.rdv.store'), [
            'nom_contact' => 'Amina',
            'moyen_contact' => '034 00 000 00',
            'prestation_id' => $prestation->id,
        ])->assertSessionHasErrors('prestation_id');
    }

    public function test_le_formulaire_peut_etre_prerempli_avec_un_modele(): void
    {
        $realisation = Realisation::factory()->create(['titre' => 'Mandala doré']);

        $this->get(route('public.rdv.create', ['realisation' => $realisation->id]))
            ->assertOk()
            ->assertSee('Mandala doré')
            ->assertSee($realisation->reference);
    }
}
