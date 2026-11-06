@extends('layouts.admin')

@php($edition = $realisation->exists)

@section('title', $edition ? "Modifier {$realisation->reference}" : 'Nouvelle réalisation')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">{{ $edition ? "Modifier la réalisation {$realisation->reference}" : 'Nouvelle réalisation' }}</h1>
        <p class="text-muted mb-0">L'image est obligatoire ; le titre est facultatif (CDC).</p>
    </div>

    <form method="POST"
          action="{{ $edition ? route('admin.realisations.update', $realisation) : route('admin.realisations.store') }}"
          enctype="multipart/form-data" class="row g-4">
        @csrf
        @if ($edition)
            @method('PUT')
        @endif

        <div class="col-12 col-lg-7">
            <div class="card card-henne">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="image" class="form-label">
                            Image principale @unless ($edition) <span class="text-danger">*</span> @endunless
                        </label>
                        <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp"
                               class="form-control @error('image') is-invalid @enderror" @unless ($edition) required @endunless>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            JPEG, PNG ou WebP, 8 Mo maximum. Variantes optimisées générées automatiquement.
                            @if ($edition) Laissez vide pour conserver l'image actuelle. @endif
                        </div>
                        @if ($edition && ($url = $realisation->urlVignette()))
                            <img src="{{ $url }}" alt="{{ $realisation->texteAlternatif() }}"
                                 class="rounded mt-2" width="120" loading="lazy">
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="titre" class="form-label">Titre <span class="text-muted">(facultatif)</span></label>
                        <input type="text" name="titre" id="titre" maxlength="150"
                               class="form-control @error('titre') is-invalid @enderror"
                               value="{{ old('titre', $realisation->titre) }}">
                        @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description courte <span class="text-muted">(facultative)</span></label>
                        <textarea name="description" id="description" rows="3" maxlength="1000"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $realisation->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="texte_alternatif" class="form-label">Texte alternatif de l'image</label>
                        <input type="text" name="texte_alternatif" id="texte_alternatif" maxlength="255"
                               class="form-control @error('texte_alternatif') is-invalid @enderror"
                               value="{{ old('texte_alternatif', $realisation->texte_alternatif) }}">
                        @error('texte_alternatif')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Décrit l'image aux technologies d'assistance. À défaut : le titre ou la référence.</div>
                    </div>

                    <div class="mb-0">
                        <label for="realisee_le" class="form-label">Date de réalisation <span class="text-muted">(facultative)</span></label>
                        <input type="date" name="realisee_le" id="realisee_le"
                               class="form-control @error('realisee_le') is-invalid @enderror"
                               value="{{ old('realisee_le', $realisation->realisee_le?->format('Y-m-d')) }}">
                        @error('realisee_le')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card card-henne mb-4">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Catégories</h2>
                    @if ($categories->isEmpty())
                        <p class="text-muted small mb-0">
                            Aucune catégorie : <a href="{{ route('admin.categories.index') }}">créez-en d'abord</a> si besoin.
                        </p>
                    @else
                        @foreach ($categories as $categorie)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]"
                                       value="{{ $categorie->id }}" id="categorie-{{ $categorie->id }}"
                                       @checked(in_array($categorie->id, old('categories', $realisation->categories->pluck('id')->all())))>
                                <label class="form-check-label" for="categorie-{{ $categorie->id }}">
                                    {{ $categorie->nom }} @unless ($categorie->visible) <span class="text-muted small">(masquée)</span> @endunless
                                </label>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="card card-henne">
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-4">
                        <input type="hidden" name="publiee" value="0">
                        <input class="form-check-input" type="checkbox" name="publiee" value="1" id="publiee"
                               @checked(old('publiee', $realisation->publiee))>
                        <label class="form-check-label" for="publiee">Publier sur le site</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-rdv"><span>{{ $edition ? 'Enregistrer' : 'Créer' }}</span></button>
                        <a href="{{ route('admin.realisations.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
