<?php

namespace Tests\Feature\Public;

use App\Models\Temoignage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemoignagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_page_temoignages_affiche_uniquement_les_publies_et_autorises(): void
    {
        Temoignage::factory()->publie()->create(['auteur' => 'Visible', 'contenu' => 'Superbe henné, je suis ravie.']);
        Temoignage::factory()->autorise()->create(['auteur' => 'AutoriseNonPublie']);
        Temoignage::factory()->create(['auteur' => 'SansAutorisation']);

        $this->get(route('public.temoignages'))
            ->assertOk()
            ->assertSee('Visible')
            ->assertSee('Superbe henné, je suis ravie.')
            ->assertDontSee('AutoriseNonPublie')
            ->assertDontSee('SansAutorisation');
    }

    public function test_la_page_reste_accueillante_sans_temoignage(): void
    {
        $this->get(route('public.temoignages'))
            ->assertOk()
            ->assertSee('Les premiers témoignages arrivent bientôt.');
    }

    public function test_l_accueil_montre_les_temoignages_publies(): void
    {
        Temoignage::factory()->publie()->create(['auteur' => 'Fatou']);

        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('Elles en parlent')
            ->assertSee('Fatou')
            ->assertSee(route('public.temoignages'));
    }

    public function test_l_accueil_masque_la_section_sans_temoignage_publie(): void
    {
        Temoignage::factory()->autorise()->create();

        $this->get(route('public.home'))
            ->assertOk()
            ->assertDontSee('Elles en parlent');
    }

    public function test_un_temoignage_depublie_disparait_du_site(): void
    {
        $temoignage = Temoignage::factory()->publie()->create(['auteur' => 'Ancienne']);

        $temoignage->update(['autorisation_affichage' => false]);

        $this->get(route('public.temoignages'))->assertDontSee('Ancienne');
        $this->get(route('public.home'))->assertDontSee('Ancienne');
    }
}
