<?php

namespace App\Support;

/**
 * Normalisation de numéros de téléphone pour les liens (wa.me, tel:).
 *
 * Aucun indicatif pays n'est présumé (la zone d'activité reste à préciser,
 * CDC §2) : le numéro doit être saisi au format international dans les
 * paramètres ; ce helper tolère les décorations courantes (+, 00, espaces,
 * points, tirets, parenthèses).
 */
class Telephone
{
    /**
     * Retourne le numéro réduit à ses chiffres (sans « + » ni « 00 » initial),
     * ou null si l'entrée est vide ou trop courte pour être exploitable.
     */
    public static function international(?string $numero): ?string
    {
        if ($numero === null) {
            return null;
        }

        $chiffres = preg_replace('/\D+/', '', $numero) ?? '';

        if (str_starts_with($chiffres, '00')) {
            $chiffres = substr($chiffres, 2);
        }

        return strlen($chiffres) >= 6 ? $chiffres : null;
    }
}
