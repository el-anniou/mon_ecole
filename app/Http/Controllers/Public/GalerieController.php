<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\Realisation;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Galerie publique des réalisations (CDC §6) :
 * filtrage par catégorie, pagination, détail avec partage et WhatsApp.
 */
class GalerieController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Categorie::query()->visibles()->ordonnees()->get();

        $categorieActive = null;

        if ($slug = $request->query('categorie')) {
            $categorieActive = $categories->firstWhere('slug', $slug);
        }

        $realisations = Realisation::query()
            ->publiees()
            ->ordonnees()
            ->when($categorieActive, fn ($q) => $q->whereHas(
                'categories', fn ($c) => $c->whereKey($categorieActive->id),
            ))
            ->paginate(12)
            ->withQueryString();

        return view('public.galerie', [
            'realisations' => $realisations,
            'categories' => $categories,
            'categorieActive' => $categorieActive,
        ]);
    }

    public function show(string $reference): View
    {
        $realisation = Realisation::query()
            ->publiees()
            ->where('reference', $reference)
            ->with(['categories' => fn ($q) => $q->visibles()->ordonnees()])
            ->firstOrFail();

        return view('public.realisation', [
            'realisation' => $realisation,
        ]);
    }
}
