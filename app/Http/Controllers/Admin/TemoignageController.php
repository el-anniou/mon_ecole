<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TemoignageRequest;
use App\Models\Temoignage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Gestion des témoignages (CDC §8). Règle d'or : jamais publié sans
 * autorisation enregistrée — le modèle Temoignage impose ce verrou,
 * le contrôleur se contente de l'expliquer à l'utilisatrice.
 */
class TemoignageController extends Controller
{
    public function index(): View
    {
        return view('admin.temoignages.index', [
            'temoignages' => Temoignage::query()->latest()->orderByDesc('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.temoignages.form', ['temoignage' => new Temoignage]);
    }

    public function store(TemoignageRequest $request): RedirectResponse
    {
        $temoignage = new Temoignage($request->safe()->all());
        $temoignage->autorisation_affichage = $request->boolean('autorisation_affichage');
        $temoignage->save();

        return redirect()->route('admin.temoignages.index')
            ->with('succes', "Témoignage de « {$temoignage->auteur} » enregistré.");
    }

    public function edit(Temoignage $temoignage): View
    {
        return view('admin.temoignages.form', ['temoignage' => $temoignage]);
    }

    public function update(TemoignageRequest $request, Temoignage $temoignage): RedirectResponse
    {
        $etaitPublie = $temoignage->publie;

        $temoignage->fill($request->safe()->all());
        $temoignage->autorisation_affichage = $request->boolean('autorisation_affichage');
        $temoignage->save();

        $message = "Témoignage de « {$temoignage->auteur} » mis à jour.";

        if ($etaitPublie && ! $temoignage->publie) {
            $message .= ' Il a été retiré du site : l\'autorisation d\'affichage a été révoquée.';
        }

        return redirect()->route('admin.temoignages.index')->with('succes', $message);
    }

    public function basculerPublication(Temoignage $temoignage): RedirectResponse
    {
        if (! $temoignage->publie && ! $temoignage->autorisation_affichage) {
            return back()->with('erreur',
                "Impossible de publier le témoignage de « {$temoignage->auteur} » : "
                .'aucune autorisation d\'affichage n\'est enregistrée.');
        }

        $temoignage->update(['publie' => ! $temoignage->publie]);

        return back()->with('succes', $temoignage->publie
            ? "Témoignage de « {$temoignage->auteur} » publié."
            : "Témoignage de « {$temoignage->auteur} » retiré du site.");
    }

    public function destroy(Temoignage $temoignage): RedirectResponse
    {
        $temoignage->delete();

        return redirect()->route('admin.temoignages.index')
            ->with('succes', "Témoignage de « {$temoignage->auteur} » supprimé.");
    }
}
