@extends('layouts.admin')

@php($edition = $prestation->exists)

@section('title', $edition ? "Modifier « {$prestation->nom} »" : 'Nouvelle prestation')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">{{ $edition ? "Modifier « {$prestation->nom} »" : 'Nouvelle prestation' }}</h1>
        <p class="text-muted mb-0">Le type de prix s'adapte à votre pratique — aucun montant fictif imposé (CDC §7).</p>
    </div>

    <form method="POST"
          action="{{ $edition ? route('admin.prestations.update', $prestation) : route('admin.prestations.store') }}"
          class="row g-4">
        @csrf
        @if ($edition)
            @method('PUT')
        @endif

        <div class="col-12 col-lg-7">
            <div class="card card-henne">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" id="nom" required maxlength="150"
                               class="form-control @error('nom') is-invalid @enderror"
                               value="{{ old('nom', $prestation->nom) }}">
                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description courte <span class="text-muted">(facultative)</span></label>
                        <textarea name="description" id="description" rows="3" maxlength="1000"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $prestation->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="type_prix" class="form-label">Type de prix <span class="text-danger">*</span></label>
                        <select name="type_prix" id="type_prix" class="form-select @error('type_prix') is-invalid @enderror">
                            @foreach (\App\Models\Prestation::TYPES_PRIX as $valeur => $libelle)
                                <option value="{{ $valeur }}" @selected(old('type_prix', $prestation->type_prix ?? 'sur_demande') === $valeur)>
                                    {{ $libelle }}
                                </option>
                            @endforeach
                        </select>
                        @error('type_prix')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">La devise s'administre dans <a href="{{ route('admin.parametres.edit') }}">Paramètres</a>.</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label for="prix_min" class="form-label">Prix / borne basse</label>
                            <input type="number" name="prix_min" id="prix_min" step="0.01" min="0"
                                   class="form-control @error('prix_min') is-invalid @enderror"
                                   value="{{ old('prix_min', $prestation->prix_min) }}">
                            @error('prix_min')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label for="prix_max" class="form-label">Borne haute <span class="text-muted">(fourchette)</span></label>
                            <input type="number" name="prix_max" id="prix_max" step="0.01" min="0"
                                   class="form-control @error('prix_max') is-invalid @enderror"
                                   value="{{ old('prix_max', $prestation->prix_max) }}">
                            @error('prix_max')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="duree_minutes" class="form-label">Durée indicative en minutes <span class="text-muted">(facultative)</span></label>
                        <input type="number" name="duree_minutes" id="duree_minutes" min="5" max="1440" step="5"
                               class="form-control @error('duree_minutes') is-invalid @enderror"
                               value="{{ old('duree_minutes', $prestation->duree_minutes) }}">
                        @error('duree_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card card-henne">
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-4">
                        <input type="hidden" name="publiee" value="0">
                        <input class="form-check-input" type="checkbox" name="publiee" value="1" id="publiee"
                               @checked(old('publiee', $prestation->publiee))>
                        <label class="form-check-label" for="publiee">Publier sur le site</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-rdv"><span>{{ $edition ? 'Enregistrer' : 'Créer' }}</span></button>
                        <a href="{{ route('admin.prestations.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
