<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ParametresRequest;
use App\Models\Parametre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Gestion des paramètres centraux (CDC §9) : nom de l'activité,
 * coordonnées, numéro WhatsApp, informations pratiques, liens sociaux.
 */
class ParametreController extends Controller
{
    /**
     * Affiche le formulaire groupé des paramètres.
     */
    public function edit(): View
    {
        $valeurs = Parametre::query()->pluck('valeur', 'cle');

        return view('admin.parametres', [
            'groupes' => config('parametres'),
            'valeurs' => $valeurs,
        ]);
    }

    /**
     * Enregistre l'ensemble des paramètres du formulaire.
     */
    public function update(ParametresRequest $request): RedirectResponse
    {
        foreach (array_keys($request->rules()) as $cle) {
            Parametre::definir($cle, $request->validated($cle));
        }

        return redirect()
            ->route('admin.parametres.edit')
            ->with('succes', 'Paramètres enregistrés.');
    }
}
