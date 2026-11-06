@extends('layouts.admin')

@section('title', 'Rendez-vous')

@php($total = $compteurs->sum())

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Rendez-vous</h1>
            <p class="text-muted mb-0">
                Les demandes arrivent « en attente » — aucune confirmation automatique
                n'est envoyée, c'est toujours vous qui répondez.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.rdv.calendrier') }}" class="btn btn-outline-secondary">Vue calendrier</a>
            <a href="{{ route('admin.rdv.create') }}" class="btn-rdv"><span>Nouveau rendez-vous</span></a>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.rdv.index') }}" class="pilule @if (! $statutActif) actif @endif">
            Tous ({{ $total }})
        </a>
        @foreach (\App\Models\RendezVous::STATUTS as $valeur => $libelle)
            <a href="{{ route('admin.rdv.index', ['statut' => $valeur]) }}"
               class="pilule @if ($statutActif === $valeur) actif @endif">
                {{ $libelle }} ({{ $compteurs[$valeur] ?? 0 }})
            </a>
        @endforeach
    </div>

    <div class="card card-henne">
        <div class="card-body p-0">
            @if ($rendezVous->isEmpty())
                <p class="text-muted p-4 mb-0">
                    {{ $statutActif ? 'Aucun rendez-vous avec ce statut.' : 'Aucune demande ni rendez-vous pour le moment.' }}
                </p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($rendezVous as $rdv)
                        @php($conflits = $rdv->conflits())
                        <li class="list-group-item bg-transparent py-3">
                            <div class="d-flex flex-wrap gap-3">
                                <div class="flex-grow-1" style="min-width: 260px;">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <strong>{{ $rdv->nom_contact }}</strong>
                                        <span class="badge {{ $rdv->couleurBadge() }}">{{ $rdv->statutLisible() }}</span>
                                        <span class="badge text-bg-dark">{{ $rdv->source === 'site' ? 'Via le site' : 'Ajout manuel' }}</span>
                                        @if ($conflits->isNotEmpty())
                                            <span class="badge text-bg-danger"
                                                  title="Chevauche : {{ $conflits->pluck('nom_contact')->implode(', ') }}">
                                                ⚠ Conflit de créneau
                                            </span>
                                        @endif
                                    </div>

                                    <div class="small mt-1">
                                        @if ($horaire = $rdv->horaireAffiche())
                                            <span class="text-info-emphasis">📅 {{ $horaire }}</span>
                                        @elseif ($souhait = $rdv->souhaitAffiche())
                                            <span class="text-muted">Souhait : {{ $souhait }}</span>
                                        @else
                                            <span class="text-muted">Aucune date exprimée</span>
                                        @endif
                                    </div>

                                    <div class="text-muted small">
                                        Contact : {{ $rdv->moyen_contact }}
                                        @if ($rdv->prestation) · {{ $rdv->prestation->nom }} @endif
                                        @if ($rdv->realisation) · modèle {{ $rdv->realisation->reference }} @endif
                                    </div>

                                    @if ($rdv->message)
                                        <div class="text-muted small fst-italic mt-1">« {{ Str::limit($rdv->message, 140) }} »</div>
                                    @endif
                                </div>

                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    @if ($lienWhatsapp = $rdv->lienWhatsappReponse())
                                        <a href="{{ $lienWhatsapp }}" target="_blank" rel="noopener"
                                           class="btn btn-outline-success btn-sm">Répondre sur WhatsApp</a>
                                    @endif

                                    <form method="POST" action="{{ route('admin.rdv.statut', $rdv) }}"
                                          class="d-flex align-items-center gap-1">
                                        @csrf
                                        <label class="visually-hidden" for="statut-{{ $rdv->id }}">Changer le statut</label>
                                        <select name="statut" id="statut-{{ $rdv->id }}" class="form-select form-select-sm w-auto">
                                            @foreach (\App\Models\RendezVous::STATUTS as $valeur => $libelle)
                                                <option value="{{ $valeur }}" @selected($rdv->statut === $valeur)>{{ $libelle }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-outline-secondary btn-sm">OK</button>
                                    </form>

                                    <a href="{{ route('admin.rdv.edit', $rdv) }}" class="btn btn-outline-secondary btn-sm">Modifier</a>

                                    <form method="POST" action="{{ route('admin.rdv.destroy', $rdv) }}"
                                          onsubmit="return confirm('Supprimer le rendez-vous de « {{ $rdv->nom_contact }} » ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-link btn-sm text-danger">Supprimer</button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
