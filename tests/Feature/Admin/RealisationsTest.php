<?php

namespace Tests\Feature\Admin;

use App\Models\Categorie;
use App\Models\Realisation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RealisationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_la_gestion_des_realisations_exige_l_authentification(): void
    {
        $this->get(route('admin.realisations.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_creation_avec_image_genere_reference_et_variantes(): void
    {
        $categorie = Categorie::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('admin.realisations.store'), [
                'image' => UploadedFile::fake()->image('motif.jpg', 1600, 2000),
                'titre' => 'Motif mariée',
                'publiee' => '1',
                'categories' => [$categorie->id],
            ])
            ->assertRedirect(route('admin.realisations.index'));

        $realisation = Realisation::sole();

        $this->assertSame('R-0001', $realisation->reference);
        $this->assertTrue($realisation->publiee);
        $this->assertTrue($realisation->categories->contains($categorie));

        Storage::disk('public')->assertExists($realisation->image_dossier.'/original.jpg');
        $this->assertNotNull($realisation->urlVignette(), 'La vignette doit exister.');
        $this->assertNotNull($realisation->urlGrande(), 'La grande variante doit exister.');
    }

    public function test_l_image_est_obligatoire_a_la_creation(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.realisations.store'), ['titre' => 'Sans image'])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('realisations', 0);
    }

    public function test_un_fichier_non_image_est_rejete(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.realisations.store'), [
                'image' => UploadedFile::fake()->create('script.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('image');
    }

    public function test_le_titre_est_facultatif(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.realisations.store'), [
                'image' => UploadedFile::fake()->image('motif.png', 800, 1000),
            ])
            ->assertRedirect(route('admin.realisations.index'));

        $this->assertNull(Realisation::sole()->titre);
    }

    public function test_bascule_de_publication(): void
    {
        $realisation = Realisation::factory()->brouillon()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('admin.realisations.publication', $realisation));

        $this->assertTrue($realisation->refresh()->publiee);
    }

    public function test_mise_a_jour_sans_nouvelle_image_conserve_l_actuelle(): void
    {
        $realisation = Realisation::factory()->create(['image_dossier' => 'realisations/conserve']);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.realisations.update', $realisation), [
                'titre' => 'Titre modifié',
                'publiee' => '1',
            ])
            ->assertRedirect(route('admin.realisations.index'));

        $realisation->refresh();
        $this->assertSame('Titre modifié', $realisation->titre);
        $this->assertSame('realisations/conserve', $realisation->image_dossier);
    }

    public function test_suppression_efface_les_images(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.realisations.store'), [
            'image' => UploadedFile::fake()->image('a-supprimer.jpg', 600, 800),
        ]);

        $realisation = Realisation::sole();
        $dossier = $realisation->image_dossier;

        $this->actingAs($admin)->delete(route('admin.realisations.destroy', $realisation));

        $this->assertDatabaseCount('realisations', 0);
        Storage::disk('public')->assertMissing($dossier.'/original.jpg');
    }
}
