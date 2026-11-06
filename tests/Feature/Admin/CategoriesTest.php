<?php

namespace Tests\Feature\Admin;

use App\Models\Categorie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_gestion_des_categories_exige_l_authentification(): void
    {
        $this->get(route('admin.categories.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_creation_d_une_categorie_avec_slug_et_position_automatiques(): void
    {
        Categorie::factory()->create(['nom' => 'Mariée', 'slug' => 'mariee', 'position' => 1]);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.categories.store'), ['nom' => 'Mariée'])
            ->assertRedirect(route('admin.categories.index'));

        $nouvelle = Categorie::query()->where('nom', 'Mariée')->whereKeyNot(1)->first();

        $this->assertNotNull($nouvelle);
        $this->assertSame('mariee-2', $nouvelle->slug, 'Le slug doit être unique.');
        $this->assertGreaterThan(1, $nouvelle->position);
    }

    public function test_renommage_et_masquage(): void
    {
        $categorie = Categorie::factory()->create(['nom' => 'Mains', 'slug' => 'mains']);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.categories.update', $categorie), [
                'nom' => 'Mains & poignets',
                'visible' => '0',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $categorie->refresh();
        $this->assertSame('Mains & poignets', $categorie->nom);
        $this->assertSame('mains-poignets', $categorie->slug);
        $this->assertFalse($categorie->visible);
    }

    public function test_reordonnancement(): void
    {
        $premiere = Categorie::factory()->create(['position' => 1]);
        $seconde = Categorie::factory()->create(['position' => 2]);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.categories.deplacer', $seconde), ['sens' => 'haut']);

        $this->assertSame(2, $premiere->refresh()->position);
        $this->assertSame(1, $seconde->refresh()->position);
    }

    public function test_suppression_conserve_les_realisations(): void
    {
        $categorie = Categorie::factory()->create();
        $realisation = \App\Models\Realisation::factory()->create();
        $realisation->categories()->attach($categorie);

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.categories.destroy', $categorie))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $categorie->id]);
        $this->assertDatabaseHas('realisations', ['id' => $realisation->id]);
    }
}
