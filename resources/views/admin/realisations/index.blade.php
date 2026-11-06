@extends('layouts.admin')

@section('title', 'Réalisations')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Réalisations</h1>
            <p class="text-muted mb-0">Les créations de la galerie : photos, publication, ordre.</p>
        </div>
        <a href="{{ route('admin.realisations.create') }}" class="btn-rdv"><span>Ajouter une réalisation</span></a>
    </div>

    <nav class="d-flex gap-2 mb-3" aria-label="Filtrer par statut">
        @foreach (['toutes' => 'Toutes', 'publiees' => 'Publiées', 'brouillons' => 'Brouillons'] as $cle => $libelle)
            <a href="{{ route('admin.realisations.index', $cle === 'toutes' ? [] : ['statut' => $cle]) }}"
               class="pilule @if ($statut === $cle) actif @endif text-decoration-none">{{ $libelle }}</a>
        @endforeach
    </nav>

    <div class="card card-henne">
        <div class="card-body p-0">
            @if ($realisations->isEmpty())
                <p class="text-muted p-4 mb-0">Aucune réalisation pour ce filtre.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($realisations as $realisation)
                        <li class="list-group-item bg-transparent py-3">
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div class="btn-group-vertical btn-group-sm" role="group" aria-label="Déplacer">
                                    <form method="POST" action="{{ route('admin.realisations.deplacer', $realisation) }}">
                                        @csrf
                                        <button class="btn btn-outline-secondary px-2 py-0 w-100" name="sens" value="haut" title="Monter">↑</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.realisations.deplacer', $realisation) }}">
                                        @csrf
                                        <button class="btn btn-outline-secondary px-2 py-0 w-100" name="sens" value="bas" title="Descendre">↓</button>
                                    </form>
                                </div>

                                @if ($url = $realisation->urlVignette())
                                    <img src="{{ $url }}" alt="{{ $realisation->texteAlternatif() }}"
                                         width="72" height="72" class="rounded object-fit-cover" loading="lazy">
                                @else
                                    <div class="rounded bg-dark-subtle d-grid place-items-center" style="width:72px;height:72px;">
                                        <span class="text-muted small m-auto">—</span>
                                    </div>
                                @endif

                                <div class="flex-grow-1">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <strong>{{ $realisation->titre ?: 'Sans titre' }}</strong>
                                        <code class="small">{{ $realisation->reference }}</code>
                                        @if ($realisation->publiee)
                                            <span class="badge text-bg-success">Publiée</span>
                                        @else
                                            <span class="badge text-bg-secondary">Brouillon</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small">
                                        {{ $realisation->categories->pluck('nom')->implode(' · ') ?: 'Sans catégorie' }}
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <form method="POST" action="{{ route('admin.realisations.publication', $realisation) }}">
                                        @csrf
                                        <button class="btn btn-outline-secondary btn-sm">
                                            {{ $realisation->publiee ? 'Dépublier' : 'Publier' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.realisations.edit', $realisation) }}"
                                       class="btn btn-outline-secondary btn-sm">Modifier</a>
                                    <form method="POST" action="{{ route('admin.realisations.destroy', $realisation) }}"
                                          onsubmit="return confirm('Supprimer définitivement cette réalisation et ses images ?');">
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

    <div class="mt-3">
        {{ $realisations->links() }}
    </div>
@endsection
