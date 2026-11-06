<?php

namespace App\Models;

use App\Support\Telephone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Paramètre administrable du site (clé/valeur).
 *
 * Idée reprise du prototype : `Parametre::valeur()` est utilisé partout
 * dans les vues publiques ; chaque clé est mise en cache indéfiniment et
 * invalidée automatiquement à l'enregistrement ou à la suppression.
 */
class Parametre extends Model
{
    protected $fillable = ['cle', 'valeur'];

    /**
     * Valeur d'un paramètre, avec défaut si absente ou vide.
     */
    public static function valeur(string $cle, ?string $defaut = null): ?string
    {
        $valeur = Cache::rememberForever(
            static::cleCache($cle),
            fn () => static::query()->where('cle', $cle)->value('valeur'),
        );

        return ($valeur !== null && $valeur !== '') ? $valeur : $defaut;
    }

    /**
     * Crée ou met à jour un paramètre.
     */
    public static function definir(string $cle, ?string $valeur): static
    {
        return static::query()->updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
    }

    /**
     * Lien WhatsApp prérempli vers le numéro administré, ou null si
     * aucun numéro n'est configuré — aucun numéro n'est inventé (CDC §6).
     */
    public static function lienWhatsapp(?string $message = null): ?string
    {
        $numero = Telephone::international(static::valeur('telephone_whatsapp'));

        if ($numero === null) {
            return null;
        }

        $lien = 'https://wa.me/'.$numero;

        return $message !== null && $message !== ''
            ? $lien.'?text='.rawurlencode($message)
            : $lien;
    }

    protected static function booted(): void
    {
        $vider = fn (self $parametre) => Cache::forget(static::cleCache($parametre->cle));

        static::saved($vider);
        static::deleted($vider);
    }

    protected static function cleCache(string $cle): string
    {
        return 'parametre.'.$cle;
    }
}
