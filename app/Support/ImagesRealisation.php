<?php

namespace App\Support;

use App\Models\Realisation;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Stockage et optimisation des images de réalisations (CDC §6) :
 *
 * - l'original est conservé tel quel dans le stockage public ;
 * - deux variantes optimisées sont générées : `vignette` (grilles) et
 *   `grande` (visionneuse), en WebP si disponible, sinon JPEG ;
 * - l'orientation EXIF est corrigée à la génération ;
 * - les noms de fichiers et répertoires sont générés (jamais le nom client).
 */
class ImagesRealisation
{
    public const LARGEUR_VIGNETTE = 480;

    public const LARGEUR_GRANDE = 1280;

    public const QUALITE = 82;

    /**
     * Enregistre l'image d'une réalisation et génère ses variantes.
     *
     * @return string le répertoire relatif sur le disque `public`
     */
    public function enregistrer(UploadedFile $fichier): string
    {
        $dossier = 'realisations/'.date('Y').'/'.Str::uuid();

        $extension = strtolower($fichier->getClientOriginalExtension() ?: 'jpg');
        $fichier->storeAs($dossier, 'original.'.$extension, 'public');

        $this->genererVariantes($dossier, Storage::disk('public')->path($dossier.'/original.'.$extension));

        return $dossier;
    }

    /**
     * Supprime le répertoire d'images d'une réalisation devenue inutile.
     */
    public function supprimer(Realisation $realisation): void
    {
        if ($realisation->image_dossier) {
            Storage::disk('public')->deleteDirectory($realisation->image_dossier);
        }
    }

    protected function genererVariantes(string $dossier, string $cheminOriginal): void
    {
        $image = $this->ouvrir($cheminOriginal);

        foreach (['vignette' => self::LARGEUR_VIGNETTE, 'grande' => self::LARGEUR_GRANDE] as $nom => $largeur) {
            $variante = $this->redimensionner($image, $largeur);
            $this->ecrire($variante, $dossier, $nom);

            if ($variante !== $image) {
                imagedestroy($variante);
            }
        }

        imagedestroy($image);
    }

    protected function ouvrir(string $chemin): GdImage
    {
        $image = match (mime_content_type($chemin) ?: '') {
            'image/jpeg' => imagecreatefromjpeg($chemin),
            'image/png' => imagecreatefrompng($chemin),
            'image/webp' => imagecreatefromwebp($chemin),
            default => false,
        };

        if ($image === false) {
            throw new RuntimeException('Format d’image non pris en charge.');
        }

        return $this->corrigerOrientation($image, $chemin);
    }

    /**
     * Applique la rotation indiquée par les métadonnées EXIF (photos mobiles).
     */
    protected function corrigerOrientation(GdImage $image, string $chemin): GdImage
    {
        if (! function_exists('exif_read_data') || mime_content_type($chemin) !== 'image/jpeg') {
            return $image;
        }

        $exif = @exif_read_data($chemin);
        $orientation = $exif['Orientation'] ?? 1;

        $tournee = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        if ($tournee !== $image && $tournee !== false) {
            imagedestroy($image);

            return $tournee;
        }

        return $image;
    }

    protected function redimensionner(GdImage $image, int $largeurMax): GdImage
    {
        $largeur = imagesx($image);
        $hauteur = imagesy($image);

        if ($largeur <= $largeurMax) {
            return $image;
        }

        $nouvelle = imagescale($image, $largeurMax, (int) round($hauteur * $largeurMax / $largeur));

        if ($nouvelle === false) {
            throw new RuntimeException('Échec du redimensionnement de l’image.');
        }

        return $nouvelle;
    }

    protected function ecrire(GdImage $image, string $dossier, string $nom): void
    {
        $disque = Storage::disk('public');
        $disque->makeDirectory($dossier);

        if (function_exists('imagewebp')) {
            imagewebp($image, $disque->path("$dossier/$nom.webp"), self::QUALITE);

            return;
        }

        imagejpeg($image, $disque->path("$dossier/$nom.jpg"), self::QUALITE);
    }
}
