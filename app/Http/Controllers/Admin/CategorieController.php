<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestion des catégories de la galerie (CDC §6) :
 * création, renommage, réordonnancement, masquage, suppression.
 */
class CategorieController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Categorie::query()->ordonnees()->withCount('realisations')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
        ], attributes: ['nom' => 'nom de la catégorie']);

        Categorie::create($donnees);

        return redirect()->route('admin.categories.index')
            ->with('succes', 'Catégorie créée.');
    }

    public function update(Request $request, Categorie $categorie): RedirectResponse
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'visible' => ['nullable', 'boolean'],
        ], attributes: ['nom' => 'nom de la catégorie']);

        $categorie->update([
            'nom' => $donnees['nom'],
            'slug' => Categorie::slugUnique($donnees['nom'], $categorie->id),
            'visible' => $request->boolean('visible'),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('succes', 'Catégorie mise à jour.');
    }

    /**
     * Échange la position avec la catégorie voisine (haut ou bas).
     */
    public function deplacer(Request $request, Categorie $categorie): RedirectResponse
    {
        $sens = $request->validate(['sens' => ['required', 'in:haut,bas']])['sens'];

        $voisine = Categorie::query()
            ->when($sens === 'haut',
                fn ($q) => $q->where('position', '<', $categorie->position)->orderByDesc('position'),
                fn ($q) => $q->where('position', '>', $categorie->position)->orderBy('position'))
            ->first();

        if ($voisine) {
            [$categorie->position, $voisine->position] = [$voisine->position, $categorie->position];
            $categorie->save();
            $voisine->save();
        }

        return redirect()->route('admin.categories.index');
    }

    /**
     * Suppression : les réalisations associées sont conservées,
     * seul le lien de catégorie disparaît (table pivot en cascade).
     */
    public function destroy(Categorie $categorie): RedirectResponse
    {
        $categorie->delete();

        return redirect()->route('admin.categories.index')
            ->with('succes', 'Catégorie supprimée. Les réalisations associées sont conservées.');
    }
}
