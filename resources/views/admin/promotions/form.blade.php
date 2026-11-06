@extends('layouts.admin')

@php($edition = $promotion->exists)

@section('title', $edition ? "Modifier « {$promotion->titre} »" : 'Nouvelle promotion')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">{{ $edition ? "Modifier « {$promotion->titre} »" : 'Nouvelle promotion' }}</h1>
        <p class="text-muted mb-0">Sans date de fin, la promotion reste en ligne jusqu'à dépublication.</p>
    </div>

    <form method="POST"
          action="{{ $edition ? route('admin.promotions.update', $promotion) : route('admin.promotions.store') }}"
          class="row g-4">
        @csrf
        @if ($edition)
            @method('PUT')
        @endif

        <div class="col-12 col-lg-7">
            <div class="card card-henne">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="titre" class="form-label">Titre <span class="text-danger">*</span></label>
                        <input type="text" name="titre" id="titre" required maxlength="150"
                               class="form-control @error('titre') is-invalid @enderror"
                               value="{{ old('titre', $promotion->titre) }}">
                        @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="texte" class="form-label">Texte <span class="text-danger">*</span></label>
                        <textarea name="texte" id="texte" rows="3" required maxlength="1000"
                                  class="form-control @error('texte') is-invalid @enderror">{{ old('texte', $promotion->texte) }}</textarea>
                        @error('texte')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label for="debut" class="form-label">Début <span class="text-muted">(facultatif)</span></label>
                            <input type="date" name="debut" id="debut"
                                   class="form-control @error('debut') is-invalid @enderror"
                                   value="{{ old('debut', $promotion->debut?->format('Y-m-d')) }}">
                            @error('debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label for="fin" class="form-label">Fin <span class="text-muted">(facultative)</span></label>
                            <input type="date" name="fin" id="fin"
                                   class="form-control @error('fin') is-invalid @enderror"
                                   value="{{ old('fin', $promotion->fin?->format('Y-m-d')) }}">
                            @error('fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="lien_action" class="form-label">Lien d'action <span class="text-muted">(facultatif)</span></label>
                        <input type="url" name="lien_action" id="lien_action" maxlength="255"
                               class="form-control @error('lien_action') is-invalid @enderror"
                               value="{{ old('lien_action', $promotion->lien_action) }}"
                               placeholder="https://…">
                        @error('lien_action')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                               @checked(old('publiee', $promotion->publiee))>
                        <label class="form-check-label" for="publiee">Publier (dans sa période de validité)</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-rdv"><span>{{ $edition ? 'Enregistrer' : 'Créer' }}</span></button>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
