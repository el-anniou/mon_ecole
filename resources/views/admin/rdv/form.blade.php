@extends('layouts.admin')

@php($edition = $rendezVous->exists)

@section('title', $edition ? "Rendez-vous de « {$rendezVous->nom_contact} »" : 'Nouveau rendez-vous')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">{{ $edition ? "Rendez-vous de « {$rendezVous->nom_contact} »" : 'Nouveau rendez-vous' }}</h1>
        <p class="text-muted mb-0">
            @if ($edition)
                {{ $rendezVous->source === 'site' ? 'Demande arrivée via le site' : 'Rendez-vous créé manuellement' }}
                le {{ $rendezVous->created_at->format('d/m/Y') }}.
            @else
                Création manuelle — pour une cliente qui vous a contactée directement.
            @endif
        </p>
    </div>

    @if ($edition && ($conflits = $rendezVous->conflits())->isNotEmpty())
        <div class="alert alert-danger">
            ⚠ Ce créneau chevauche : {{ $conflits->pluck('nom_contact')->implode(', ') }}.
            Rien n'est bloqué — c'est vous qui décidez.
        </div>
    @endif

    <form method="POST"
          action="{{ $edition ? route('admin.rdv.update', $rendezVous) : route('admin.rdv.store') }}"
          class="row g-4">
        @csrf
        @if ($edition)
            @method('PUT')
        @endif

        <div class="col-12 col-lg-7">
            <div class="card card-henne mb-4">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted mb-3">Cliente</h2>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="nom_contact" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom_contact" id="nom_contact" required maxlength="100"
                                   class="form-control @error('nom_contact') is-invalid @enderror"
                                   value="{{ old('nom_contact', $rendezVous->nom_contact) }}">
                            @error('nom_contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="moyen_contact" class="form-label">Moyen de contact <span class="text-danger">*</span></label>
                            <input type="text" name="moyen_contact" id="moyen_contact" required maxlength="150"
                                   class="form-control @error('moyen_contact') is-invalid @enderror"
                                   value="{{ old('moyen_contact', $rendezVous->moyen_contact) }}">
                            @error('moyen_contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="prestation_id" class="form-label">Prestation</label>
                            <select name="prestation_id" id="prestation_id" class="form-select">
                                <option value="">—</option>
                                @foreach ($prestations as $prestation)
                                    <option value="{{ $prestation->id }}"
                                        @selected((int) old('prestation_id', $rendezVous->prestation_id) === $prestation->id)>
                                        {{ $prestation->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="realisation_id" class="form-label">Modèle concerné</label>
                            <select name="realisation_id" id="realisation_id" class="form-select">
                                <option value="">—</option>
                                @foreach ($realisations as $realisation)
                                    <option value="{{ $realisation->id }}"
                                        @selected((int) old('realisation_id', $rendezVous->realisation_id) === $realisation->id)>
                                        {{ $realisation->reference }} — {{ $realisation->titre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-henne">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted mb-3">Souhait exprimé</h2>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="date_souhaitee" class="form-label">Date souhaitée</label>
                            <input type="date" name="date_souhaitee" id="date_souhaitee"
                                   class="form-control @error('date_souhaitee') is-invalid @enderror"
                                   value="{{ old('date_souhaitee', $rendezVous->date_souhaitee?->format('Y-m-d')) }}">
                            @error('date_souhaitee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="periode_souhaitee" class="form-label">Période souhaitée</label>
                            <select name="periode_souhaitee" id="periode_souhaitee" class="form-select">
                                <option value="">—</option>
                                @foreach (\App\Models\RendezVous::PERIODES as $valeur => $libelle)
                                    <option value="{{ $valeur }}"
                                        @selected(old('periode_souhaitee', $rendezVous->periode_souhaitee) === $valeur)>
                                        {{ $libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label for="message" class="form-label">Message de la cliente</label>
                        <textarea name="message" id="message" rows="3" maxlength="1500"
                                  class="form-control @error('message') is-invalid @enderror">{{ old('message', $rendezVous->message) }}</textarea>
                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card card-henne">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted mb-3">Planification</h2>

                    <div class="mb-3">
                        <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                        <select name="statut" id="statut" required
                                class="form-select @error('statut') is-invalid @enderror">
                            @foreach (\App\Models\RendezVous::STATUTS as $valeur => $libelle)
                                <option value="{{ $valeur }}"
                                    @selected(old('statut', $rendezVous->statut ?? 'confirme') === $valeur)>
                                    {{ $libelle }}
                                </option>
                            @endforeach
                        </select>
                        @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="debut" class="form-label">Date et heure du rendez-vous</label>
                        <input type="datetime-local" name="debut" id="debut"
                               class="form-control @error('debut') is-invalid @enderror"
                               value="{{ old('debut', $rendezVous->debut?->format('Y-m-d\TH:i')) }}">
                        <div class="form-text">À fixer au moment de la confirmation.</div>
                        @error('debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="duree_minutes" class="form-label">Durée (minutes)</label>
                        <input type="number" name="duree_minutes" id="duree_minutes" min="5" max="600" step="5"
                               class="form-control @error('duree_minutes') is-invalid @enderror"
                               value="{{ old('duree_minutes', $rendezVous->duree_minutes) }}">
                        @error('duree_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-0">
                        <label for="notes_privees" class="form-label">Notes privées</label>
                        <textarea name="notes_privees" id="notes_privees" rows="3" maxlength="2000"
                                  class="form-control @error('notes_privees') is-invalid @enderror"
                                  placeholder="Jamais visibles sur le site public.">{{ old('notes_privees', $rendezVous->notes_privees) }}</textarea>
                        @error('notes_privees')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button class="btn-rdv"><span>{{ $edition ? 'Enregistrer' : 'Créer le rendez-vous' }}</span></button>
                <a href="{{ route('admin.rdv.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </div>
    </form>
@endsection
