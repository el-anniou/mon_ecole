@extends('layouts.admin')

@section('title', 'Témoignages')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Témoignages</h1>
            <p class="text-muted mb-0">
                Règle d'or : un témoignage n'est <strong>jamais</strong> publié sans autorisation
                d'affichage enregistrée. Révoquer l'autorisation le retire aussitôt du site.
            </p>
        </div>
        <a href="{{ route('admin.temoignages.create') }}" class="btn-rdv"><span>Ajouter un témoignage</span></a>
    </div>

    <div class="card card-henne">
        <div class="card-body p-0">
            @if ($temoignages->isEmpty())
                <p class="text-muted p-4 mb-0">Aucun témoignage enregistré.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($temoignages as $temoignage)
                        <li class="list-group-item bg-transparent py-3">
                            <div class="d-flex flex-wrap align-items-start gap-3">
                                <div class="flex-grow-1" style="min-width: 240px;">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <strong>{{ $temoignage->auteur }}</strong>
                                        @php($statut = $temoignage->statut())
                                        <span class="badge
                                            @if ($statut === 'Publié') text-bg-success
                                            @elseif ($statut === 'Autorisé, non publié') text-bg-info
                                            @else text-bg-warning @endif">{{ $statut }}</span>
                                    </div>
                                    <div class="text-muted small mt-1">{{ Str::limit($temoignage->contenu, 120) }}</div>
                                    @if ($autorisation = $temoignage->autorisationAffichee())
                                        <div class="text-muted small">Autorisation {{ $autorisation }}</div>
                                    @endif
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    @if ($temoignage->autorisation_affichage)
                                        <form method="POST" action="{{ route('admin.temoignages.publication', $temoignage) }}">
                                            @csrf
                                            <button class="btn btn-outline-secondary btn-sm">
                                                {{ $temoignage->publie ? 'Retirer du site' : 'Publier' }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small" title="Enregistrez d'abord l'autorisation d'affichage">
                                            Publication verrouillée
                                        </span>
                                    @endif
                                    <a href="{{ route('admin.temoignages.edit', $temoignage) }}"
                                       class="btn btn-outline-secondary btn-sm">Modifier</a>
                                    <form method="POST" action="{{ route('admin.temoignages.destroy', $temoignage) }}"
                                          onsubmit="return confirm('Supprimer le témoignage de « {{ $temoignage->auteur }} » ?');">
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
