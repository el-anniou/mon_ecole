<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\Prestation;
use App\Models\Promotion;
use App\Models\Realisation;
use App\Models\Temoignage;
use Illuminate\View\View;

/**
 * Page d'accueil publique (CDC §5) : présentation, sélection de
 * réalisations, promotions actives, résumé des prestations, appels à
 * l'action. Chaque section disparaît si son contenu n'existe pas —
 * jamais de donnée inventée.
 */
class AccueilController extends Controller
{
    public function __invoke(): View
    {
        return view('public.home', [
            'selection' => Realisation::query()->publiees()->ordonnees()->take(6)->get(),
            'categories' => Categorie::query()->visibles()->ordonnees()
                ->whereHas('realisations', fn ($q) => $q->publiees())
                ->get(),
            'promotions' => Promotion::query()->actives()->orderBy('fin')->take(3)->get(),
            'prestations' => Prestation::query()->publiees()->ordonnees()->take(3)->get(),
            'temoignages' => Temoignage::query()->publies()->latest()->orderByDesc('id')->take(3)->get(),
        ]);
    }
}
