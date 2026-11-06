@extends('layouts.admin')

@php($edition = $temoignage->exists)

@section('title', $edition ? "Modifier le témoignage de « {$temoignage->auteur} »" : 'Nouveau témoignage')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">{{ $edition ? "Modifier le témoignage de « {$temoignage->auteur} »" : 'Nouveau témoignage' }}</h1>
        <p class="text-muted mb-0">
            La publication n'est possible qu'après avoir coché l'autorisation d'affichage.
        </p>
    </div>

    <form method="POST"
          action="{{ $edition ? route('admin.temoignages.update', $temoignage) : route('admin.temoignages.store') }}"
          class="row g-4">
        @csrf
        @if ($edition)
            @method('PUT')
        @endif

        <div class="col-12 col-lg-7">
            <div class="card card-henne">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="auteur" class="form-label">Prénom ou pseudonyme <span class="text-danger">*</span></label>
                        <input type="text" name="auteur" id="auteur" required maxlength="100"
                               class="form-control @error('auteur') is-invalid @enderror"
                               value="{{ old('auteur', $temoignage->auteur) }}">
                        <div class="form-text">Selon ce que la cliente a accepté : prénom seul, initiale, pseudonyme…</div>
                        @error('auteur')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-0">
                        <label for="contenu" class="form-label">Témoignage <span class="text-danger">*</span></label>
                        <textarea name="contenu" id="contenu" rows="5" required maxlength="2000"
                                  class="form-control @error('contenu') is-invalid @enderror">{{ old('contenu', $temoignage->contenu) }}</textarea>
                        @error('contenu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card card-henne">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted mb-3">Autorisation d'affichage</h2>

                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="autorisation_affichage" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" value="1"
                               name="autorisation_affichage" id="autorisation_affichage"
                               @checked(old('autorisation_affichage', $temoignage->autorisation_affichage))>
                        <label class="form-check-label" for="autorisation_affichage">
                            La cliente a donné son autorisation d'affichage
                        </label>
                    </div>

                    <div class="mb-3">
                        <label for="autorisation_notee_le" class="form-label">Autorisation notée le</label>
                        <input type="date" name="autorisation_notee_le" id="autorisation_notee_le"
                               max="{{ now()->toDateString() }}"
                               class="form-control @error('autorisation_notee_le') is-invalid @enderror"
                               value="{{ old('autorisation_notee_le', $temoignage->autorisation_notee_le?->format('Y-m-d')) }}">
                        <div class="form-text">Laissez vide pour enregistrer la date du jour.</div>
                        @error('autorisation_notee_le')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-0">
                        <label for="autorisation_note" class="form-label">Comment l'accord a été donné <span class="text-muted">(facultatif)</span></label>
                        <input type="text" name="autorisation_note" id="autorisation_note" maxlength="255"
                               class="form-control @error('autorisation_note') is-invalid @enderror"
                               value="{{ old('autorisation_note', $temoignage->autorisation_note) }}"
                               placeholder="Ex. : accord par WhatsApp le 12/01">
                        @error('autorisation_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <p class="text-muted small mt-3 mb-0">
                        Si l'autorisation est décochée, le témoignage est automatiquement retiré du site.
                    </p>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button class="btn-rdv"><span>{{ $edition ? 'Enregistrer' : 'Créer le témoignage' }}</span></button>
                <a href="{{ route('admin.temoignages.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </div>
    </form>
@endsection
