<?php

namespace Tests\Feature\Admin;

use App\Models\Temoignage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemoignagesTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create();
    }

    public function test_la_gestion_des_temoignages_exige_une_connexion(): void
    {
        $this->get(route('admin.temoignages.index'))->assertRedirect(route('admin.login'));
    }

    public function test_la_liste_affiche_les_temoignages_et_leur_statut(): void
    {
        Temoignage::factory()->create(['auteur' => 'Sans-Accord']);
        Temoignage::factory()->autorise()->create(['auteur' => 'Accord-Seul']);
        Temoignage::factory()->publie()->create(['auteur' => 'En-Ligne']);

        $this->actingAs($this->admin())
            ->get(route('admin.temoignages.index'))
            ->assertOk()
            ->assertSeeInOrder(['En-Ligne', 'Accord-Seul', 'Sans-Accord'])
            ->assertSee('Sans autorisation')
            ->assertSee('Autorisé, non publié')
            ->assertSee('Publié')
            ->assertSee('Publication verrouillée');
    }

    public function test_creation_d_un_temoignage_sans_autorisation(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.temoignages.store'), [
                'auteur' => 'Aïcha',
                'contenu' => 'Un très beau motif, merci !',
            ])
            ->assertRedirect(route('admin.temoignages.index'));

        $this->assertDatabaseHas('temoignages', [
            'auteur' => 'Aïcha',
            'autorisation_affichage' => false,
            'publie' => false,
        ]);
    }

    public function test_accorder_l_autorisation_sans_date_trace_la_date_du_jour(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.temoignages.store'), [
                'auteur' => 'Mina',
                'contenu' => 'Je recommande vivement.',
                'autorisation_affichage' => '1',
            ]);

        $temoignage = Temoignage::query()->firstWhere('auteur', 'Mina');

        $this->assertTrue($temoignage->autorisation_affichage);
        $this->assertSame(now()->toDateString(), $temoignage->autorisation_notee_le->toDateString());
    }

    public function test_impossible_de_publier_sans_autorisation(): void
    {
        $temoignage = Temoignage::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.temoignages.publication', $temoignage))
            ->assertSessionHas('erreur');

        $this->assertFalse($temoignage->fresh()->publie);
    }

    public function test_le_modele_verrouille_la_publication_meme_en_forcant(): void
    {
        // Défense en profondeur : même un code qui contourne le contrôleur
        // ne peut pas enregistrer publie=true sans autorisation.
        $temoignage = Temoignage::factory()->create();
        $temoignage->publie = true;
        $temoignage->save();

        $this->assertFalse($temoignage->fresh()->publie);
    }

    public function test_publication_possible_avec_autorisation(): void
    {
        $temoignage = Temoignage::factory()->autorise()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.temoignages.publication', $temoignage))
            ->assertSessionHas('succes');

        $this->assertTrue($temoignage->fresh()->publie);
    }

    public function test_revoquer_l_autorisation_depublie_automatiquement(): void
    {
        $temoignage = Temoignage::factory()->publie()->create(['auteur' => 'Leïla']);

        $this->actingAs($this->admin())
            ->put(route('admin.temoignages.update', $temoignage), [
                'auteur' => 'Leïla',
                'contenu' => $temoignage->contenu,
                'autorisation_affichage' => '0',
            ])
            ->assertRedirect(route('admin.temoignages.index'));

        $temoignage->refresh();
        $this->assertFalse($temoignage->autorisation_affichage);
        $this->assertFalse($temoignage->publie, 'La révocation doit retirer le témoignage du site.');
    }

    public function test_la_date_d_autorisation_ne_peut_pas_etre_future(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.temoignages.store'), [
                'auteur' => 'Sonia',
                'contenu' => 'Magnifique.',
                'autorisation_affichage' => '1',
                'autorisation_notee_le' => now()->addDay()->toDateString(),
            ])
            ->assertSessionHasErrors('autorisation_notee_le');
    }

    public function test_suppression_d_un_temoignage(): void
    {
        $temoignage = Temoignage::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.temoignages.destroy', $temoignage))
            ->assertRedirect(route('admin.temoignages.index'));

        $this->assertDatabaseMissing('temoignages', ['id' => $temoignage->id]);
    }
}
