<?php

namespace Tests\Feature\Public;

use App\Models\Parametre;
use App\Models\Prestation;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrestationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seules_les_prestations_publiees_sont_visibles(): void
    {
        Prestation::factory()->create(['nom' => 'Henné mariée visible']);
        Prestation::factory()->masquee()->create(['nom' => 'Prestation masquée secrète']);

        $this->get(route('public.prestations'))
            ->assertOk()
            ->assertSee('Henné mariée visible')
            ->assertDontSee('Prestation masquée secrète');
    }

    public function test_les_types_de_prix_s_affichent_correctement(): void
    {
        Parametre::definir('devise', '€');

        Prestation::factory()->create(['nom' => 'Fixe', 'type_prix' => 'fixe', 'prix_min' => 50]);
        Prestation::factory()->create(['nom' => 'Mini', 'type_prix' => 'a_partir_de', 'prix_min' => 30]);
        Prestation::factory()->create(['nom' => 'Four', 'type_prix' => 'fourchette', 'prix_min' => 40, 'prix_max' => 80.50]);
        Prestation::factory()->surDemande()->create(['nom' => 'Devis']);
        Prestation::factory()->create(['nom' => 'Cache', 'type_prix' => 'non_affiche', 'prix_min' => 999]);

        $reponse = $this->get(route('public.prestations'))->assertOk();

        $reponse->assertSee('50 €')
            ->assertSee('À partir de 30 €')
            ->assertSee('40 € – 80,50 €')
            ->assertSee('Prix sur demande')
            ->assertDontSee('999');
    }

    public function test_le_prix_s_affiche_sans_devise_si_aucune_configuree(): void
    {
        Prestation::factory()->create(['nom' => 'Sans devise', 'type_prix' => 'fixe', 'prix_min' => 25]);

        $this->get(route('public.prestations'))
            ->assertOk()
            ->assertSee('25');
    }

    public function test_une_promotion_active_apparait_sur_prestations_et_accueil(): void
    {
        Promotion::factory()->create(['titre' => 'Offre active spéciale']);

        $this->get(route('public.prestations'))->assertOk()->assertSee('Offre active spéciale');
        $this->get(route('public.home'))->assertOk()->assertSee('Offre active spéciale');
    }

    public function test_une_promotion_expiree_disparait_du_site_public(): void
    {
        Promotion::factory()->expiree()->create(['titre' => 'Offre expirée fantôme']);

        $this->get(route('public.prestations'))->assertOk()->assertDontSee('Offre expirée fantôme');
        $this->get(route('public.home'))->assertOk()->assertDontSee('Offre expirée fantôme');
    }

    public function test_une_promotion_a_venir_ou_depubliee_reste_cachee(): void
    {
        Promotion::factory()->aVenir()->create(['titre' => 'Offre future cachée']);
        Promotion::factory()->depubliee()->create(['titre' => 'Offre dépubliée cachée']);

        $this->get(route('public.prestations'))
            ->assertOk()
            ->assertDontSee('Offre future cachée')
            ->assertDontSee('Offre dépubliée cachée');
    }

    public function test_une_promotion_sans_dates_suit_la_publication(): void
    {
        Promotion::factory()->create(['titre' => 'Permanente', 'debut' => null, 'fin' => null, 'publiee' => true]);

        $this->get(route('public.prestations'))->assertOk()->assertSee('Permanente');
    }
}
