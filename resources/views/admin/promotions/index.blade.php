@extends('layouts.admin')

@section('title', 'Promotions')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Promotions</h1>
            <p class="text-muted mb-0">
                Une promotion n'apparaît sur le site que publiée et dans sa période de validité ;
                expirée, elle disparaît automatiquement. L'historique reste consultable ici.
            </p>
        </div>
        <a href="{{ route('admin.promotions.create') }}" class="btn-rdv"><span>Ajouter une promotion</span></a>
    </div>

    <div class="card card-henne">
        <div class="card-body p-0">
            @if ($promotions->isEmpty())
                <p class="text-muted p-4 mb-0">Aucune promotion enregistrée.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($promotions as $promotion)
                        <li class="list-group-item bg-transparent py-3">
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <strong>{{ $promotion->titre }}</strong>
                                        @php($statut = $promotion->statut())
                                        <span class="badge
                                            @if ($statut === 'En ligne') text-bg-success
                                            @elseif ($statut === 'À venir') text-bg-info
                                            @elseif ($statut === 'Expirée') text-bg-warning
                                            @else text-bg-secondary @endif">{{ $statut }}</span>
                                    </div>
                                    <div class="text-muted small">
                                        {{ $promotion->periodeAffichee() ?? 'Sans période définie' }}
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <form method="POST" action="{{ route('admin.promotions.publication', $promotion) }}">
                                        @csrf
                                        <button class="btn btn-outline-secondary btn-sm">
                                            {{ $promotion->publiee ? 'Dépublier' : 'Publier' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.promotions.edit', $promotion) }}"
                                       class="btn btn-outline-secondary btn-sm">Modifier</a>
                                    <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}"
                                          onsubmit="return confirm('Supprimer la promotion « {{ $promotion->titre }} » ?');">
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
