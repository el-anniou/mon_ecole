@extends('layouts.admin')

@section('title', 'Prestations')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Prestations</h1>
            <p class="text-muted mb-0">Offres, tarifs et durées affichés sur le site.</p>
        </div>
        <a href="{{ route('admin.prestations.create') }}" class="btn-rdv"><span>Ajouter une prestation</span></a>
    </div>

    <div class="card card-henne">
        <div class="card-body p-0">
            @if ($prestations->isEmpty())
                <p class="text-muted p-4 mb-0">Aucune prestation pour l'instant.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($prestations as $prestation)
                        <li class="list-group-item bg-transparent py-3">
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div class="btn-group-vertical btn-group-sm" role="group" aria-label="Déplacer">
                                    <form method="POST" action="{{ route('admin.prestations.deplacer', $prestation) }}">
                                        @csrf
                                        <button class="btn btn-outline-secondary px-2 py-0 w-100" name="sens" value="haut" title="Monter">↑</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.prestations.deplacer', $prestation) }}">
                                        @csrf
                                        <button class="btn btn-outline-secondary px-2 py-0 w-100" name="sens" value="bas" title="Descendre">↓</button>
                                    </form>
                                </div>

                                <div class="flex-grow-1">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <strong>{{ $prestation->nom }}</strong>
                                        @if ($prestation->publiee)
                                            <span class="badge text-bg-success">Publiée</span>
                                        @else
                                            <span class="badge text-bg-secondary">Masquée</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small">
                                        {{ $prestation->prixAffiche() ?? 'Prix non affiché' }}
                                        @if ($duree = $prestation->dureeAffichee()) · {{ $duree }} @endif
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <form method="POST" action="{{ route('admin.prestations.publication', $prestation) }}">
                                        @csrf
                                        <button class="btn btn-outline-secondary btn-sm">
                                            {{ $prestation->publiee ? 'Masquer' : 'Publier' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.prestations.edit', $prestation) }}"
                                       class="btn btn-outline-secondary btn-sm">Modifier</a>
                                    <form method="POST" action="{{ route('admin.prestations.destroy', $prestation) }}"
                                          onsubmit="return confirm('Supprimer la prestation « {{ $prestation->nom }} » ?');">
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
