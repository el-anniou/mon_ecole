<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromotionRequest;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Gestion des promotions (CDC §7). L'administration conserve l'accès
 * aux promotions passées ; le site public ne montre que les actives.
 */
class PromotionController extends Controller
{
    public function index(): View
    {
        return view('admin.promotions.index', [
            'promotions' => Promotion::query()->orderByDesc('fin')->orderByDesc('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.promotions.form', ['promotion' => new Promotion]);
    }

    public function store(PromotionRequest $request): RedirectResponse
    {
        $promotion = new Promotion($request->safe()->all());
        $promotion->publiee = $request->boolean('publiee');
        $promotion->save();

        return redirect()->route('admin.promotions.index')
            ->with('succes', "Promotion « {$promotion->titre} » créée.");
    }

    public function edit(Promotion $promotion): View
    {
        return view('admin.promotions.form', ['promotion' => $promotion]);
    }

    public function update(PromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $promotion->fill($request->safe()->all());
        $promotion->publiee = $request->boolean('publiee');
        $promotion->save();

        return redirect()->route('admin.promotions.index')
            ->with('succes', "Promotion « {$promotion->titre} » mise à jour.");
    }

    public function basculerPublication(Promotion $promotion): RedirectResponse
    {
        $promotion->update(['publiee' => ! $promotion->publiee]);

        return back()->with('succes', $promotion->publiee
            ? "Promotion « {$promotion->titre} » publiée."
            : "Promotion « {$promotion->titre} » dépubliée.");
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        $promotion->delete();

        return redirect()->route('admin.promotions.index')
            ->with('succes', 'Promotion supprimée.');
    }
}
