<?php

namespace Tests\Feature\Admin;

use App\Models\Parametre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParametresTest extends TestCase
{
    use RefreshDatabase;

    public function test_l_ecran_parametres_exige_l_authentification(): void
    {
        $this->get(route('admin.parametres.edit'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_l_ecran_parametres_s_affiche(): void
    {
        Parametre::definir('nom_marque', 'Ma Marque Test');

        $this->actingAs(User::factory()->create())
            ->get(route('admin.parametres.edit'))
            ->assertOk()
            ->assertSee('Paramètres')
            ->assertSee('Numéro WhatsApp')
            ->assertSee('Ma Marque Test');
    }

    public function test_l_enregistrement_persiste_les_valeurs(): void
    {
        $reponse = $this->actingAs(User::factory()->create())
            ->put(route('admin.parametres.update'), [
                'nom_marque' => 'HENNÉ TEST',
                'telephone_whatsapp' => '+261 34 00 000 00',
                'instagram_url' => 'https://instagram.com/exemple',
            ]);

        $reponse->assertRedirect(route('admin.parametres.edit'))
            ->assertSessionHas('succes');

        $this->assertDatabaseHas('parametres', ['cle' => 'nom_marque', 'valeur' => 'HENNÉ TEST']);
        $this->assertDatabaseHas('parametres', ['cle' => 'telephone_whatsapp', 'valeur' => '+261 34 00 000 00']);
        $this->assertSame('HENNÉ TEST', Parametre::valeur('nom_marque'));
    }

    public function test_une_url_invalide_est_rejetee(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('admin.parametres.edit'))
            ->put(route('admin.parametres.update'), [
                'instagram_url' => 'pas-une-url',
            ])
            ->assertRedirect(route('admin.parametres.edit'))
            ->assertSessionHasErrors('instagram_url');
    }

    public function test_un_email_invalide_est_rejete(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('admin.parametres.edit'))
            ->put(route('admin.parametres.update'), [
                'email' => 'pas-un-email',
            ])
            ->assertRedirect(route('admin.parametres.edit'))
            ->assertSessionHasErrors('email');
    }
}
