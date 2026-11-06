<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\DemandeRendezVousRequest;
use App\Models\Prestation;
use App\Models\Realisation;
use App\Models\RendezVous;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Demande de rendez-vous publique, sans compte (CDC §8).
 * Aucune confirmation automatique : la demande arrive « en attente »
 * dans l'administration, la réponse est toujours personnelle.
 */
class RendezVousController extends Controller
{
    public function create(Request $request): View
    {
        // Pré-remplissages facultatifs (liens « ce modèle » / « cette prestation »).
        $realisation = $request->filled('realisation')
            ? Realisation::query()->publiees()->find($request->integer('realisation'))
            : null;

        return view('public.rendez-vous', [
            'prestations' => Prestation::query()->publiees()->ordonnees()->get(),
            'prestationChoisie' => $request->integer('prestation') ?: null,
            'realisation' => $realisation,
        ]);
    }

    public function store(DemandeRendezVousRequest $request): RedirectResponse
    {
        // Piège à robots : le champ « site_web » est invisible pour un humain.
        // S'il est rempli, on fait mine d'accepter sans rien enregistrer.
        if (! $request->filled('site_web')) {
            RendezVous::query()->create([
                ...$request->safe()->all(),
                'statut' => 'en_attente',
                'source' => 'site',
            ]);
        }

        return redirect()->route('public.rdv.create')
            ->with('demande_envoyee', true);
    }
}
