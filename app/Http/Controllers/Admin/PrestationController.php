<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PrestationRequest;
use App\Models\Prestation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestion des prestations (CDC §7) : nom, description, type de prix,
 * durée indicative, ordre et publication.
 */
class PrestationController extends Controller
{
    public function index(): View
    {
        return view('admin.prestations.index', [
            'prestations' => Prestation::query()->ordonnees()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.prestations.form', ['prestation' => new Prestation]);
    }

    public function store(PrestationRequest $request): RedirectResponse
    {
        $prestation = new Prestation($this->donnees($request));
        $prestation->save();

        return redirect()->route('admin.prestations.index')
            ->with('succes', "Prestation « {$prestation->nom} » créée.");
    }

    public function edit(Prestation $prestation): View
    {
        return view('admin.prestations.form', ['prestation' => $prestation]);
    }

    public function update(PrestationRequest $request, Prestation $prestation): RedirectResponse
    {
        $prestation->update($this->donnees($request));

        return redirect()->route('admin.prestations.index')
            ->with('succes', "Prestation « {$prestation->nom} » mise à jour.");
    }

    public function basculerPublication(Prestation $prestation): RedirectResponse
    {
        $prestation->update(['publiee' => ! $prestation->publiee]);

        return back()->with('succes', $prestation->publiee
            ? "Prestation « {$prestation->nom} » publiée."
            : "Prestation « {$prestation->nom} » masquée.");
    }

    public function deplacer(Request $request, Prestation $prestation): RedirectResponse
    {
        $sens = $request->validate(['sens' => ['required', 'in:haut,bas']])['sens'];

        $voisine = Prestation::query()
            ->when($sens === 'haut',
                fn ($q) => $q->where('position', '<', $prestation->position)->orderByDesc('position'),
                fn ($q) => $q->where('position', '>', $prestation->position)->orderBy('position'))
            ->first();

        if ($voisine) {
            [$prestation->position, $voisine->position] = [$voisine->position, $prestation->position];
            $prestation->save();
            $voisine->save();
        }

        return back();
    }

    public function destroy(Prestation $prestation): RedirectResponse
    {
        $prestation->delete();

        return redirect()->route('admin.prestations.index')
            ->with('succes', 'Prestation supprimée.');
    }

    /**
     * Nettoie les montants hors sujet pour le type de prix choisi.
     *
     * @return array<string, mixed>
     */
    protected function donnees(PrestationRequest $request): array
    {
        $donnees = $request->safe()->all();
        $donnees['publiee'] = $request->boolean('publiee');

        if (! in_array($donnees['type_prix'], ['fixe', 'a_partir_de', 'fourchette'])) {
            $donnees['prix_min'] = null;
            $donnees['prix_max'] = null;
        } elseif ($donnees['type_prix'] !== 'fourchette') {
            $donnees['prix_max'] = null;
        }

        return $donnees;
    }
}
