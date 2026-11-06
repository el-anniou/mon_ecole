<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RealisationRequest;
use App\Models\Categorie;
use App\Models\Realisation;
use App\Support\ImagesRealisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestion des réalisations de la galerie (CDC §6) :
 * photos, visibilité brouillon/publiée, ordre, catégories multiples.
 */
class RealisationController extends Controller
{
    public function __construct(private readonly ImagesRealisation $images)
    {
    }

    public function index(Request $request): View
    {
        $realisations = Realisation::query()
            ->with('categories')
            ->ordonnees()
            ->when($request->query('statut') === 'publiees', fn ($q) => $q->publiees())
            ->when($request->query('statut') === 'brouillons', fn ($q) => $q->where('publiee', false))
            ->paginate(20)
            ->withQueryString();

        return view('admin.realisations.index', [
            'realisations' => $realisations,
            'statut' => $request->query('statut', 'toutes'),
        ]);
    }

    public function create(): View
    {
        return view('admin.realisations.form', [
            'realisation' => new Realisation,
            'categories' => Categorie::query()->ordonnees()->get(),
        ]);
    }

    public function store(RealisationRequest $request): RedirectResponse
    {
        $realisation = new Realisation($request->safe()->except('image', 'categories'));
        $realisation->publiee = $request->boolean('publiee');
        $realisation->image_dossier = $this->images->enregistrer($request->file('image'));
        $realisation->save();

        $realisation->categories()->sync($request->validated('categories', []));

        return redirect()->route('admin.realisations.index')
            ->with('succes', "Réalisation {$realisation->reference} créée.");
    }

    public function edit(Realisation $realisation): View
    {
        return view('admin.realisations.form', [
            'realisation' => $realisation,
            'categories' => Categorie::query()->ordonnees()->get(),
        ]);
    }

    public function update(RealisationRequest $request, Realisation $realisation): RedirectResponse
    {
        $realisation->fill($request->safe()->except('image', 'categories'));
        $realisation->publiee = $request->boolean('publiee');

        if ($request->hasFile('image')) {
            $this->images->supprimer($realisation);
            $realisation->image_dossier = $this->images->enregistrer($request->file('image'));
        }

        $realisation->save();
        $realisation->categories()->sync($request->validated('categories', []));

        return redirect()->route('admin.realisations.index')
            ->with('succes', "Réalisation {$realisation->reference} mise à jour.");
    }

    /**
     * Bascule brouillon ↔ publiée.
     */
    public function basculerPublication(Realisation $realisation): RedirectResponse
    {
        $realisation->update(['publiee' => ! $realisation->publiee]);

        return back()->with('succes', $realisation->publiee
            ? "Réalisation {$realisation->reference} publiée."
            : "Réalisation {$realisation->reference} repassée en brouillon.");
    }

    /**
     * Échange la position avec la réalisation voisine.
     */
    public function deplacer(Request $request, Realisation $realisation): RedirectResponse
    {
        $sens = $request->validate(['sens' => ['required', 'in:haut,bas']])['sens'];

        $voisine = Realisation::query()
            ->when($sens === 'haut',
                fn ($q) => $q->where('position', '<', $realisation->position)->orderByDesc('position'),
                fn ($q) => $q->where('position', '>', $realisation->position)->orderBy('position'))
            ->first();

        if ($voisine) {
            [$realisation->position, $voisine->position] = [$voisine->position, $realisation->position];
            $realisation->save();
            $voisine->save();
        }

        return back();
    }

    public function destroy(Realisation $realisation): RedirectResponse
    {
        $this->images->supprimer($realisation);
        $realisation->delete();

        return redirect()->route('admin.realisations.index')
            ->with('succes', 'Réalisation supprimée, images comprises.');
    }
}
