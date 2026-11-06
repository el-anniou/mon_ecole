<?php

namespace Tests\Feature;

use App\Models\Parametre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParametreModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_valeur_retourne_le_defaut_si_la_cle_est_absente(): void
    {
        $this->assertNull(Parametre::valeur('inconnue'));
        $this->assertSame('défaut', Parametre::valeur('inconnue', 'défaut'));
    }

    public function test_valeur_retourne_le_defaut_si_la_valeur_est_vide(): void
    {
        Parametre::definir('slogan', '');

        $this->assertSame('défaut', Parametre::valeur('slogan', 'défaut'));
    }

    public function test_le_cache_est_invalide_a_l_enregistrement(): void
    {
        Parametre::definir('nom_marque', 'Avant');
        $this->assertSame('Avant', Parametre::valeur('nom_marque'));

        Parametre::definir('nom_marque', 'Après');
        $this->assertSame('Après', Parametre::valeur('nom_marque'));
    }

    public function test_lien_whatsapp_sans_numero_configure(): void
    {
        $this->assertNull(Parametre::lienWhatsapp('Bonjour'));
    }

    public function test_lien_whatsapp_avec_numero_et_message(): void
    {
        Parametre::definir('telephone_whatsapp', '+261 34 00 000 00');

        $this->assertSame(
            'https://wa.me/261340000000?text=Bonjour%2C%20je%20souhaite%20prendre%20rendez-vous.',
            Parametre::lienWhatsapp('Bonjour, je souhaite prendre rendez-vous.'),
        );

        $this->assertSame('https://wa.me/261340000000', Parametre::lienWhatsapp());
    }
}
