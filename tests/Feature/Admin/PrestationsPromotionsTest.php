<?php

namespace Tests\Feature\Admin;

use App\Models\Prestation;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrestationsPromotionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_gestion_exige_l_authentification(): void
    {
        $this->get(route('admin.prestations.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.promotions.index'))->assertRedirect(route('admin.login'));
    }

    public function test_creation_d_une_prestation_a_prix_fixe(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.prestations.store'), [
                'nom' => 'Henné mariée',
                'type_prix' => 'fixe',
                'prix_min' => '60',
                'duree_minutes' => '90',
                'publiee' => '1',
            ])
            ->assertRedirect(route('admin.prestations.index'));

        $prestation = Prestation::sole();
        $this->assertSame('Henné mariée', $prestation->nom);
        $this->assertTrue($prestation->publiee);
        $this->assertSame('1 h 30', $prestation->dureeAffichee());
    }

    public function test_un_montant_est_exige_pour_un_prix_fixe(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.prestations.store'), [
                'nom' => 'Sans montant',
                'type_prix' => 'fixe',
            ])
            ->assertSessionHasErrors('prix_min');
    }

    public function test_une_fourchette_invalide_est_rejetee(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.prestations.store'), [
                'nom' => 'Fourchette inversée',
                'type_prix' => 'fourchette',
                'prix_min' => '80',
                'prix_max' => '40',
            ])
            ->assertSessionHasErrors('prix_max');
    }

    public function test_les_montants_sont_purges_pour_un_prix_sur_demande(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.prestations.store'), [
                'nom' => 'Sur demande',
                'type_prix' => 'sur_demande',
                'prix_min' => '50',
                'prix_max' => '90',
            ]);

        $prestation = Prestation::sole();
        $this->assertNull($prestation->prix_min);
        $this->assertNull($prestation->prix_max);
    }

    public function test_creation_et_statuts_d_une_promotion(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.promotions.store'), [
                'titre' => 'Offre de lancement',
                'texte' => 'Texte de l\'offre.',
                'debut' => now()->subDay()->toDateString(),
                'fin' => now()->addDays(5)->toDateString(),
                'publiee' => '1',
            ])
            ->assertRedirect(route('admin.promotions.index'));

        $this->assertSame('En ligne', Promotion::sole()->statut());
    }

    public function test_une_date_de_fin_avant_le_debut_est_rejetee(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.promotions.store'), [
                'titre' => 'Dates inversées',
                'texte' => 'Texte.',
                'debut' => '2026-12-10',
                'fin' => '2026-12-01',
            ])
            ->assertSessionHasErrors('fin');
    }

    public function test_l_administration_conserve_les_promotions_expirees(): void
    {
        Promotion::factory()->expiree()->create(['titre' => 'Ancienne offre archivée']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.promotions.index'))
            ->assertOk()
            ->assertSee('Ancienne offre archivée')
            ->assertSee('Expirée');
    }
}
