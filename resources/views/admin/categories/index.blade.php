@extends('layouts.admin')

@section('title', 'Catégories')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Catégories</h1>
            <p class="text-muted mb-0">Organisation de la galerie : ordre, visibilité, renommage.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card card-henne">
                <div class="card-body p-0">
                    @if ($categories->isEmpty())
                        <p class="text-muted p-4 mb-0">Aucune catégorie pour l'instant : créez la première ci-contre.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($categories as $categorie)
                                <li class="list-group-item bg-transparent py-3">
                                    <form method="POST" action="{{ route('admin.categories.update', $categorie) }}"
                                          class="d-flex flex-wrap align-items-center gap-2">
                                        @csrf
                                        @method('PUT')

                                        <div class="d-flex flex-column gap-1">
                                            <div class="btn-group-vertical btn-group-sm" role="group" aria-label="Déplacer">
                                                <button type="submit" class="btn btn-outline-secondary px-2 py-0"
                                                        formaction="{{ route('admin.categories.deplacer', $categorie) }}"
                                                        name="sens" value="haut" @disabled($loop->first) title="Monter">↑</button>
                                                <button type="submit" class="btn btn-outline-secondary px-2 py-0"
                                                        formaction="{{ route('admin.categories.deplacer', $categorie) }}"
                                                        name="sens" value="bas" @disabled($loop->last) title="Descendre">↓</button>
                                            </div>
                                        </div>

                                        <div class="flex-grow-1">
                                            <input type="text" name="nom" value="{{ $categorie->nom }}"
                                                   class="form-control form-control-sm" aria-label="Nom de la catégorie">
                                            <div class="form-text">
                                                {{ $categorie->realisations_count }} réalisation(s) · /{{ $categorie->slug }}
                                            </div>
                                        </div>

                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="visible" value="0">
                                            <input class="form-check-input" type="checkbox" name="visible" value="1"
                                                   id="visible-{{ $categorie->id }}" @checked($categorie->visible)>
                                            <label class="form-check-label small" for="visible-{{ $categorie->id }}">Visible</label>
                                        </div>

                                        <button type="submit" class="btn btn-outline-secondary btn-sm">Enregistrer</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.categories.destroy', $categorie) }}"
                                          class="mt-2 text-end"
                                          onsubmit="return confirm('Supprimer la catégorie « {{ $categorie->nom }} » ? Les réalisations associées seront conservées.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link btn-sm text-danger p-0">Supprimer</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card card-henne">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Nouvelle catégorie</h2>
                    <form method="POST" action="{{ route('admin.categories.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" name="nom" id="nom" required maxlength="100"
                                   class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}">
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Exemples : Mariée, Mains, Pieds, Minimaliste…</div>
                        </div>
                        <button type="submit" class="btn-rdv"><span>Créer la catégorie</span></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
