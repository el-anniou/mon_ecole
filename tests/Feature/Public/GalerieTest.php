<?php

namespace Tests\Feature\Public;

use App\Models\Categorie;
use App\Models\Parametre;
use App\Models\Realisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalerieTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_galerie_n_affiche_que_les_realisations_publiees(): void
    {
        Realisation::factory()->create(['titre' => 'Publiée visible']);
        Realisation::factory()->brouillon()->create(['titre' => 'Brouillon caché']);

        $this->get(route('public.galerie'))
            ->assertOk()
            ->assertSee('Publiée visible')
            ->assertDontSee('Brouillon caché');
    }

    public function test_le_filtre_par_categorie_fonctionne(): void
    {
        $mariee = Categorie::factory()->create(['nom' => 'Mariée', 'slug' => 'mariee']);
        $dansCategorie = Realisation::factory()->create(['titre' => 'Modèle mariée']);
        $dansCategorie->categories()->attach($mariee);
        Realisation::factory()->create(['titre' => 'Modèle hors catégorie']);

        $this->get(route('public.galerie', ['categorie' => 'mariee']))
            ->assertOk()
            ->assertSee('Modèle mariée')
            ->assertDontSee('Modèle hors catégorie');
    }

    public function test_etat_vide_coherent_quand_aucun_resultat(): void
    {
        Categorie::factory()->create(['nom' => 'Pieds', 'slug' => 'pieds']);

        $this->get(route('public.galerie', ['categorie' => 'pieds']))
            ->assertOk()
            ->assertSee('Aucune réalisation dans');
    }

    public function test_une_categorie_masquee_n_apparait_pas_dans_les_filtres(): void
    {
        Categorie::factory()->create(['nom' => 'Visible publique']);
        Categorie::factory()->masquee()->create(['nom' => 'Masquée secrète']);
        Realisation::factory()->create();

        $this->get(route('public.galerie'))
            ->assertOk()
            ->assertSee('Visible publique')
            ->assertDontSee('Masquée secrète');
    }

    public function test_detail_d_une_realisation_publiee(): void
    {
        $realisation = Realisation::factory()->create(['titre' => 'Motif précieux']);

        $this->get(route('public.realisation', $realisation->reference))
            ->assertOk()
            ->assertSee('Motif précieux')
            ->assertSee($realisation->reference);
    }

    public function test_detail_d_un_brouillon_introuvable(): void
    {
        $brouillon = Realisation::factory()->brouillon()->create();

        $this->get(route('public.realisation', $brouillon->reference))
            ->assertNotFound();
    }

    public function test_bouton_je_veux_ce_modele_present_quand_whatsapp_configure(): void
    {
        Parametre::definir('telephone_whatsapp', '+261 34 00 000 00');
        $realisation = Realisation::factory()->create();

        $this->get(route('public.realisation', $realisation->reference))
            ->assertOk()
            ->assertSee('Je veux ce modèle')
            ->assertSee('wa.me/261340000000', escape: false)
            ->assertSee(rawurlencode($realisation->reference), escape: false);
    }

    public function test_bouton_je_veux_ce_modele_absent_sans_numero(): void
    {
        $realisation = Realisation::factory()->create();

        $this->get(route('public.realisation', $realisation->reference))
            ->assertOk()
            ->assertDontSee('Je veux ce modèle');
    }

    public function test_l_accueil_affiche_la_selection_publiee(): void
    {
        Realisation::factory()->create(['titre' => 'En vitrine']);

        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('En vitrine');
    }
}
