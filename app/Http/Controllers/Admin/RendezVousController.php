<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RendezVousRequest;
use App\Models\Prestation;
use App\Models\Realisation;
use App\Models\RendezVous;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Gestion des demandes et rendez-vous (CDC §9) : liste filtrable par
 * statut, calendrier mensuel, création manuelle, signalement des
 * conflits de créneaux. Aucune confirmation automatique n'est envoyée.
 */
class RendezVousController extends Controller
{
    public function index(Request $request): View
    {
        $statut = $request->query('statut');

        if (! array_key_exists((string) $statut, RendezVous::STATUTS)) {
            $statut = null;
        }

        $rendezVous = RendezVous::query()
            ->with(['prestation', 'realisation'])
            ->when($statut, fn ($q) => $q->where('statut', $statut))
            // Les demandes en attente d'abord, puis du plus proche créneau au plus ancien.
            ->orderByRaw("CASE WHEN statut = 'en_attente' THEN 0 ELSE 1 END")
            ->orderByRaw('COALESCE(debut, date_souhaitee, created_at) DESC')
            ->get();

        return view('admin.rdv.index', [
            'rendezVous' => $rendezVous,
            'statutActif' => $statut,
            'compteurs' => RendezVous::query()
                ->selectRaw('statut, COUNT(*) AS total')
                ->groupBy('statut')
                ->pluck('total', 'statut'),
        ]);
    }

    public function calendrier(Request $request): View
    {
        $mois = Carbon::createFromFormat('Y-m', $request->query('mois', now()->format('Y-m')))
            ?->startOfMonth() ?? now()->startOfMonth();

        $debutGrille = $mois->copy()->startOfWeek(Carbon::MONDAY);
        $finGrille = $mois->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $planifies = RendezVous::query()
            ->whereNotNull('debut')
            ->whereBetween('debut', [$debutGrille, $finGrille->copy()->endOfDay()])
            ->orderBy('debut')
            ->get()
            ->groupBy(fn (RendezVous $rdv) => $rdv->debut->toDateString());

        // Les demandes en attente apparaissent sur leur date souhaitée.
        $souhaits = RendezVous::query()
            ->enAttente()
            ->whereNotNull('date_souhaitee')
            ->whereBetween('date_souhaitee', [$debutGrille->toDateString(), $finGrille->toDateString()])
            ->orderBy('date_souhaitee')
            ->get()
            ->groupBy(fn (RendezVous $rdv) => $rdv->date_souhaitee->toDateString());

        $jourChoisi = null;

        if ($request->filled('jour')) {
            try {
                $jourChoisi = Carbon::createFromFormat('Y-m-d', $request->query('jour'))->startOfDay();
            } catch (\Throwable) {
                $jourChoisi = null;
            }
        }

        return view('admin.rdv.calendrier', [
            'mois' => $mois,
            'debutGrille' => $debutGrille,
            'finGrille' => $finGrille,
            'planifies' => $planifies,
            'souhaits' => $souhaits,
            'jourChoisi' => $jourChoisi,
        ]);
    }

    public function create(): View
    {
        return view('admin.rdv.form', [
            'rendezVous' => new RendezVous(['statut' => 'confirme', 'duree_minutes' => 60]),
            'prestations' => Prestation::query()->ordonnees()->get(),
            'realisations' => Realisation::query()->orderBy('reference')->get(),
        ]);
    }

    public function store(RendezVousRequest $request): RedirectResponse
    {
        $rendezVous = new RendezVous($request->safe()->all());
        $rendezVous->source = 'manuel';
        $rendezVous->save();

        return redirect()->route('admin.rdv.index')
            ->with('succes', $this->messageEnregistrement($rendezVous, 'créé'));
    }

    public function edit(RendezVous $rendezVous): View
    {
        return view('admin.rdv.form', [
            'rendezVous' => $rendezVous,
            'prestations' => Prestation::query()->ordonnees()->get(),
            'realisations' => Realisation::query()->orderBy('reference')->get(),
        ]);
    }

    public function update(RendezVousRequest $request, RendezVous $rendezVous): RedirectResponse
    {
        $rendezVous->fill($request->safe()->all());
        $rendezVous->save();

        return redirect()->route('admin.rdv.index')
            ->with('succes', $this->messageEnregistrement($rendezVous, 'mis à jour'));
    }

    /**
     * Changement rapide de statut depuis la liste ou le calendrier.
     */
    public function changerStatut(Request $request, RendezVous $rendezVous): RedirectResponse
    {
        $valide = $request->validate([
            'statut' => ['required', Rule::in(array_keys(RendezVous::STATUTS))],
        ]);

        $rendezVous->update(['statut' => $valide['statut']]);

        $message = "Rendez-vous de « {$rendezVous->nom_contact} » : ".mb_strtolower($rendezVous->statutLisible()).'.';

        if ($rendezVous->statut === 'confirme' && ! $rendezVous->debut) {
            $message .= ' Pensez à fixer la date et l\'heure du rendez-vous.';
        }

        return back()->with('succes', $message);
    }

    public function destroy(RendezVous $rendezVous): RedirectResponse
    {
        $rendezVous->delete();

        return redirect()->route('admin.rdv.index')
            ->with('succes', "Rendez-vous de « {$rendezVous->nom_contact} » supprimé.");
    }

    protected function messageEnregistrement(RendezVous $rendezVous, string $action): string
    {
        $message = "Rendez-vous de « {$rendezVous->nom_contact} » {$action}.";

        if ($rendezVous->statut === 'confirme' && ! $rendezVous->debut) {
            $message .= ' Pensez à fixer la date et l\'heure.';
        } elseif ($conflits = $rendezVous->conflits()->pluck('nom_contact')->implode(', ')) {
            $message .= " Attention : créneau en conflit avec {$conflits}.";
        }

        return $message;
    }
}
