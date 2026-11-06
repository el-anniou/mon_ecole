<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Realisation;
use App\Support\ImagesRealisation;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

/**
 * Données de DÉMONSTRATION pour le développement uniquement.
 *
 * Ne jamais exécuter en production : les contenus réels seront saisis par
 * la propriétaire (CDC phase 7). Les « photos » sont des motifs abstraits
 * générés par GD, étiquetés comme exemples.
 *
 * Usage : php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('DemoSeeder est réservé au développement.');

            return;
        }

        $categories = collect(['Mariée', 'Mains', 'Pieds', 'Minimaliste'])
            ->map(fn (string $nom) => Categorie::query()->firstOrCreate(
                ['slug' => str($nom)->slug()->toString()],
                ['nom' => $nom],
            ));

        $images = app(ImagesRealisation::class);

        // Idempotence : les réalisations de démo sont créées en une seule
        // fournée (certaines n'ont pas de titre, on ne peut donc pas les
        // retrouver individuellement — on saute le bloc si la galerie existe).
        $galerieDejaCreee = Realisation::query()->exists();

        foreach ($galerieDejaCreee ? [] : range(1, 9) as $i) {

            $fichier = $this->motifTemporaire($i);

            $realisation = new Realisation([
                'titre' => $i % 3 === 0 ? null : "Motif d'exemple $i", // certains sans titre (CDC)
                'description' => $i % 2 === 0 ? 'Description d’exemple pour le développement.' : null,
                'publiee' => $i !== 9, // un brouillon pour tester le filtre
            ]);
            $realisation->image_dossier = $images->enregistrer($fichier);
            $realisation->save();

            $realisation->categories()->sync(
                $categories->random(random_int(1, 2))->pluck('id')->all(),
            );

            @unlink($fichier->getPathname());
        }

        $this->prestationsEtPromotions();
        $this->temoignages();
        $this->rendezVous();

        $this->command?->info('Données de démonstration créées (réalisations, catégories, prestations, promotions, témoignages, rendez-vous).');
    }

    /**
     * Demandes et rendez-vous d'exemple (étape 6) : demandes en attente,
     * rendez-vous confirmés (dont deux en conflit volontaire pour montrer
     * le signalement), un terminé et un refusé.
     */
    protected function rendezVous(): void
    {
        $exemples = [
            ['nom_contact' => 'Amina (exemple)', 'moyen_contact' => '+261 34 00 000 01', 'statut' => 'en_attente', 'source' => 'site',
                'date_souhaitee' => now()->addDays(6)->toDateString(), 'periode_souhaitee' => 'matin',
                'message' => 'Demande d’exemple : henné pour un mariage, mains et pieds.'],
            ['nom_contact' => 'Hasina (exemple)', 'moyen_contact' => 'hasina@exemple.test', 'statut' => 'en_attente', 'source' => 'site',
                'date_souhaitee' => now()->addDays(9)->toDateString(), 'periode_souhaitee' => 'soir',
                'message' => 'Demande d’exemple : motif minimaliste pour une fête.'],
            ['nom_contact' => 'Faniry (exemple)', 'moyen_contact' => '+261 34 00 000 02', 'statut' => 'confirme', 'source' => 'site',
                'debut' => now()->addDays(2)->setTime(10, 0), 'duree_minutes' => 90,
                'notes_privees' => 'Note privée d’exemple : prévoir le cône sans ammoniaque.'],
            ['nom_contact' => 'Mirana (exemple)', 'moyen_contact' => '+261 34 00 000 03', 'statut' => 'confirme', 'source' => 'manuel',
                'debut' => now()->addDays(2)->setTime(11, 0), 'duree_minutes' => 60,
                'notes_privees' => 'Chevauche volontairement le RDV de Faniry : démonstration du signalement de conflit.'],
            ['nom_contact' => 'Voara (exemple)', 'moyen_contact' => '+261 34 00 000 04', 'statut' => 'termine', 'source' => 'manuel',
                'debut' => now()->subDays(8)->setTime(15, 0), 'duree_minutes' => 60],
            ['nom_contact' => 'Lova (exemple)', 'moyen_contact' => 'lova@exemple.test', 'statut' => 'refuse', 'source' => 'site',
                'date_souhaitee' => now()->subDays(3)->toDateString(), 'periode_souhaitee' => 'apres_midi'],
        ];

        foreach ($exemples as $donnees) {
            \App\Models\RendezVous::query()->firstOrCreate(
                ['nom_contact' => $donnees['nom_contact']],
                $donnees,
            );
        }
    }

    /**
     * Témoignages d'exemple (étape 5) : deux publiés avec autorisation
     * tracée, un autorisé non publié, un sans autorisation (verrouillé).
     */
    protected function temoignages(): void
    {
        $temoignages = [
            ['auteur' => 'Nora (exemple)', 'contenu' => 'Témoignage d’exemple : un motif magnifique pour mon mariage, je recommande de tout cœur.',
                'autorisation_affichage' => true, 'autorisation_note' => 'Accord par WhatsApp (démo)', 'publie' => true],
            ['auteur' => 'S. (exemple)', 'contenu' => 'Témoignage d’exemple : accueil chaleureux et henné qui a tenu presque trois semaines.',
                'autorisation_affichage' => true, 'autorisation_note' => 'Accord de vive voix (démo)', 'publie' => true],
            ['auteur' => 'Imane (exemple)', 'contenu' => 'Témoignage d’exemple : autorisé mais pas encore publié, invisible sur le site.',
                'autorisation_affichage' => true, 'autorisation_note' => 'Accord par message (démo)', 'publie' => false],
            ['auteur' => 'Cliente discrète (exemple)', 'contenu' => 'Témoignage d’exemple : reçu sans autorisation d’affichage, la publication est verrouillée.',
                'autorisation_affichage' => false, 'autorisation_note' => null, 'publie' => false],
        ];

        foreach ($temoignages as $donnees) {
            \App\Models\Temoignage::query()->firstOrCreate(['auteur' => $donnees['auteur']], $donnees);
        }
    }

    /**
     * Prestations et promotions d'exemple (étape 4).
     */
    protected function prestationsEtPromotions(): void
    {
        \App\Models\Parametre::query()->firstOrCreate(['cle' => 'devise'], ['valeur' => '€']);

        $prestations = [
            ['nom' => 'Henné mariée (exemple)', 'type_prix' => 'fourchette', 'prix_min' => 80, 'prix_max' => 150, 'duree_minutes' => 180,
                'description' => 'Description d’exemple : motifs complets mains et pieds pour le grand jour.'],
            ['nom' => 'Henné fête (exemple)', 'type_prix' => 'a_partir_de', 'prix_min' => 30, 'prix_max' => null, 'duree_minutes' => 60,
                'description' => 'Description d’exemple : un motif élégant pour vos occasions.'],
            ['nom' => 'Motif minimaliste (exemple)', 'type_prix' => 'fixe', 'prix_min' => 15, 'prix_max' => null, 'duree_minutes' => 30,
                'description' => null],
            ['nom' => 'Projet personnalisé (exemple)', 'type_prix' => 'sur_demande', 'prix_min' => null, 'prix_max' => null, 'duree_minutes' => null,
                'description' => 'Description d’exemple : dessin sur mesure selon votre inspiration.'],
        ];

        foreach ($prestations as $donnees) {
            \App\Models\Prestation::query()->firstOrCreate(
                ['nom' => $donnees['nom']],
                [...$donnees, 'publiee' => true],
            );
        }

        \App\Models\Promotion::query()->firstOrCreate(
            ['titre' => 'Offre découverte (exemple)'],
            [
                'texte' => 'Texte d’exemple pour le développement : offre visible car active et publiée.',
                'debut' => now()->subDays(2)->toDateString(),
                'fin' => now()->addDays(14)->toDateString(),
                'publiee' => true,
            ],
        );

        \App\Models\Promotion::query()->firstOrCreate(
            ['titre' => 'Offre passée (exemple)'],
            [
                'texte' => 'Promotion expirée : visible en admin, jamais sur le site public.',
                'debut' => now()->subDays(40)->toDateString(),
                'fin' => now()->subDays(10)->toDateString(),
                'publiee' => true,
            ],
        );
    }

    /**
     * Dessine un motif abstrait « henné » et le retourne comme fichier uploadé.
     */
    protected function motifTemporaire(int $graine): UploadedFile
    {
        mt_srand($graine * 97);

        $largeur = 900;
        $hauteur = 1125;
        $image = imagecreatetruecolor($largeur, $hauteur);

        // Fond dégradé brun sombre
        for ($y = 0; $y < $hauteur; $y++) {
            $t = $y / $hauteur;
            $couleur = imagecolorallocate(
                $image,
                (int) (32 + 14 * $t),
                (int) (22 + 8 * $t),
                (int) (19 + 6 * $t),
            );
            imageline($image, 0, $y, $largeur, $y, $couleur);
        }

        $cuivre = imagecolorallocate($image, 201, 138, 91);
        $or = imagecolorallocate($image, 232, 200, 147);
        imagesetthickness($image, 3);

        // Arabesques concentriques
        $cx = $largeur / 2;
        $cy = $hauteur / 2;

        foreach (range(1, 6) as $anneau) {
            $rayon = $anneau * 70 + mt_rand(-12, 12);
            imageellipse($image, (int) $cx, (int) $cy, $rayon * 2, $rayon * 2, $anneau % 2 ? $cuivre : $or);
        }

        foreach (range(0, 11) as $branche) {
            $angle = deg2rad($branche * 30 + mt_rand(-8, 8));
            $r1 = 90;
            $r2 = 90 + mt_rand(180, 320);
            imageline(
                $image,
                (int) ($cx + $r1 * cos($angle)), (int) ($cy + $r1 * sin($angle)),
                (int) ($cx + $r2 * cos($angle)), (int) ($cy + $r2 * sin($angle)),
                $branche % 2 ? $cuivre : $or,
            );
            imagefilledellipse(
                $image,
                (int) ($cx + $r2 * cos($angle)), (int) ($cy + $r2 * sin($angle)),
                14, 14, $branche % 2 ? $or : $cuivre,
            );
        }

        // Mention explicite « exemple »
        imagestring($image, 3, 12, $hauteur - 24, 'EXEMPLE DEV '.$graine, $or);

        $chemin = tempnam(sys_get_temp_dir(), 'demo').'.jpg';
        imagejpeg($image, $chemin, 90);
        imagedestroy($image);

        return new UploadedFile($chemin, "motif-exemple-$graine.jpg", 'image/jpeg', null, true);
    }
}
