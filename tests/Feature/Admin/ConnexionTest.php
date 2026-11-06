<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConnexionTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_page_de_connexion_s_affiche(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Espace d\'administration', escape: false);
    }

    public function test_connexion_reussie_avec_identifiants_valides(): void
    {
        $admin = User::factory()->create();

        $reponse = $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $reponse->assertRedirect(route('admin.dashboard'));
    }

    public function test_connexion_refusee_avec_mot_de_passe_invalide(): void
    {
        $admin = User::factory()->create();

        $reponse = $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'mauvais-mot-de-passe',
        ]);

        $this->assertGuest();
        $reponse->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');
    }

    public function test_les_routes_admin_exigent_l_authentification(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_le_tableau_de_bord_s_affiche_pour_une_administratrice_connectee(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Tableau de bord');
    }

    public function test_une_administratrice_connectee_est_redirigee_depuis_la_page_de_connexion(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.login'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_deconnexion(): void
    {
        $admin = User::factory()->create();

        $reponse = $this->actingAs($admin)->post(route('admin.logout'));

        $this->assertGuest();
        $reponse->assertRedirect(route('public.home'));
    }

    public function test_limitation_des_tentatives_apres_cinq_echecs(): void
    {
        $admin = User::factory()->create(['email' => 'limitation@exemple.test']);

        foreach (range(1, 5) as $tentative) {
            $this->post(route('admin.login.store'), [
                'email' => $admin->email,
                'password' => 'mauvais-mot-de-passe',
            ]);
        }

        $reponse = $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $reponse->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Trop de tentatives',
            session('errors')->first('email'),
        );
    }
}
