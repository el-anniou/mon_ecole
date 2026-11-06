<?php

namespace Tests\Feature\Admin;

use App\Models\RendezVous;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RendezVousTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create();
    }

    public function test_la_gestion_des_rendez_vous_exige_une_connexion(): void
    {
        $this->get(route('admin.rdv.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.rdv.calendrier'))->assertRedirect(route('admin.login'));
    }

    public function test_la_liste_affiche_demandes_et_rendez_vous(): void
    {
        RendezVous::factory()->create(['nom_contact' => 'Demande-Site']);
        RendezVous::factory()->confirme()->manuel()->create(['nom_contact' => 'Rdv-Manuel']);

        $this->actingAs($this->admin())
            ->get(route('admin.rdv.index'))
            ->assertOk()
            ->assertSee('Demande-Site')
            ->assertSee('Rdv-Manuel')
            ->assertSee('En attente')
            ->assertSee('Confirmé')
            ->assertSee('Via le site')
            ->assertSee('Ajout manuel');
    }

    public function test_la_liste_se_filtre_par_statut(): void
    {
        RendezVous::factory()->create(['nom_contact' => 'EnAttente-Unique']);
        RendezVous::factory()->confirme()->create(['nom_contact' => 'Confirme-Unique']);

        $this->actingAs($this->admin())
            ->get(route('admin.rdv.index', ['statut' => 'confirme']))
            ->assertOk()
            ->assertSee('Confirme-Unique')
            ->assertDontSee('EnAttente-Unique');
    }

    public function test_changement_rapide_de_statut(): void
    {
        $rdv = RendezVous::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.rdv.statut', $rdv), ['statut' => 'refuse'])
            ->assertSessionHas('succes');

        $this->assertSame('refuse', $rdv->fresh()->statut);
    }

    public function test_confirmer_sans_creneau_rappelle_de_fixer_l_heure(): void
    {
        $rdv = RendezVous::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.rdv.statut', $rdv), ['statut' => 'confirme']);

        $this->assertStringContainsString('fixer la date', session('succes'));
    }

    public function test_un_statut_inconnu_est_rejete(): void
    {
        $rdv = RendezVous::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.rdv.statut', $rdv), ['statut' => 'invalide'])
            ->assertSessionHasErrors('statut');
    }

    public function test_creation_manuelle_d_un_rendez_vous(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.rdv.store'), [
                'nom_contact' => 'Cliente directe',
                'moyen_contact' => '034 11 111 11',
                'statut' => 'confirme',
                'debut' => now()->addDays(3)->setTime(14, 0)->format('Y-m-d\TH:i'),
                'duree_minutes' => 90,
            ])->assertRedirect(route('admin.rdv.index'));

        $this->assertDatabaseHas('rendez_vous', [
            'nom_contact' => 'Cliente directe',
            'statut' => 'confirme',
            'source' => 'manuel',
            'duree_minutes' => 90,
        ]);
    }

    public function test_les_conflits_de_creneaux_sont_signales(): void
    {
        $creneau = now()->addDays(2)->setTime(10, 0);

        RendezVous::factory()->create([
            'nom_contact' => 'Premiere', 'statut' => 'confirme',
            'debut' => $creneau, 'duree_minutes' => 60,
        ]);
        RendezVous::factory()->create([
            'nom_contact' => 'Seconde', 'statut' => 'confirme',
            'debut' => $creneau->copy()->addMinutes(30), 'duree_minutes' => 60,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.rdv.index'))
            ->assertSee('Conflit de créneau');
    }

    public function test_pas_de_conflit_entre_creneaux_disjoints(): void
    {
        $creneau = now()->addDays(2)->setTime(10, 0);

        RendezVous::factory()->create([
            'statut' => 'confirme', 'debut' => $creneau, 'duree_minutes' => 60,
        ]);
        RendezVous::factory()->create([
            'statut' => 'confirme', 'debut' => $creneau->copy()->addHours(2), 'duree_minutes' => 60,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.rdv.index'))
            ->assertDontSee('Conflit de créneau');
    }

    public function test_un_rdv_annule_ne_cree_pas_de_conflit(): void
    {
        $creneau = now()->addDays(2)->setTime(10, 0);

        RendezVous::factory()->create([
            'statut' => 'annule', 'debut' => $creneau, 'duree_minutes' => 60,
        ]);
        $rdv = RendezVous::factory()->create([
            'statut' => 'confirme', 'debut' => $creneau->copy()->addMinutes(15), 'duree_minutes' => 60,
        ]);

        $this->assertTrue($rdv->conflits()->isEmpty());
    }

    public function test_le_calendrier_affiche_les_rendez_vous_du_mois(): void
    {
        $rdv = RendezVous::factory()->create([
            'nom_contact' => 'SurCalendrier', 'statut' => 'confirme',
            'debut' => now()->addDays(3)->setTime(15, 0), 'duree_minutes' => 60,
        ]);
        RendezVous::factory()->create([
            'nom_contact' => 'SouhaitVisible',
            'date_souhaitee' => now()->addDays(4)->toDateString(),
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.rdv.calendrier', ['mois' => now()->addDays(3)->format('Y-m')]))
            ->assertOk()
            ->assertSee('SurCalendrier');

        $this->actingAs($this->admin())
            ->get(route('admin.rdv.calendrier', [
                'mois' => $rdv->debut->format('Y-m'),
                'jour' => $rdv->debut->toDateString(),
            ]))->assertOk()->assertSee('Modifier');
    }

    public function test_bouton_whatsapp_seulement_pour_un_contact_telephonique(): void
    {
        RendezVous::factory()->create(['moyen_contact' => '+261 34 00 000 00']);

        $this->actingAs($this->admin())
            ->get(route('admin.rdv.index'))
            ->assertSee('Répondre sur WhatsApp');

        RendezVous::query()->delete();
        RendezVous::factory()->create(['moyen_contact' => 'cliente@exemple.test']);

        $this->actingAs($this->admin())
            ->get(route('admin.rdv.index'))
            ->assertDontSee('Répondre sur WhatsApp');
    }

    public function test_modification_et_suppression(): void
    {
        $rdv = RendezVous::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.rdv.update', $rdv), [
                'nom_contact' => $rdv->nom_contact,
                'moyen_contact' => $rdv->moyen_contact,
                'statut' => 'confirme',
                'debut' => now()->addDays(2)->setTime(11, 0)->format('Y-m-d\TH:i'),
                'duree_minutes' => 120,
                'notes_privees' => 'Prévoir motif mains + pieds.',
            ])->assertRedirect(route('admin.rdv.index'));

        $rdv->refresh();
        $this->assertSame('confirme', $rdv->statut);
        $this->assertSame(120, $rdv->duree_minutes);
        $this->assertSame('site', $rdv->source, 'La source d\'origine est conservée.');

        $this->actingAs($this->admin())
            ->delete(route('admin.rdv.destroy', $rdv));

        $this->assertDatabaseMissing('rendez_vous', ['id' => $rdv->id]);
    }
}
