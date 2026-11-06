@extends('layouts.admin')

@section('title', 'Calendrier des rendez-vous')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Calendrier</h1>
            <p class="text-muted mb-0">
                Rendez-vous planifiés, et demandes en attente (pointillés) sur leur date souhaitée.
                Cliquez sur un jour pour le détailler.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.rdv.index') }}" class="btn btn-outline-secondary">Vue liste</a>
            <a href="{{ route('admin.rdv.create') }}" class="btn-rdv"><span>Nouveau rendez-vous</span></a>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a class="btn btn-outline-secondary btn-sm"
           href="{{ route('admin.rdv.calendrier', ['mois' => $mois->copy()->subMonth()->format('Y-m')]) }}">
            ← {{ ucfirst($mois->copy()->subMonth()->translatedFormat('F')) }}
        </a>
        <h2 class="h5 mb-0 text-capitalize">{{ $mois->translatedFormat('F Y') }}</h2>
        <a class="btn btn-outline-secondary btn-sm"
           href="{{ route('admin.rdv.calendrier', ['mois' => $mois->copy()->addMonth()->format('Y-m')]) }}">
            {{ ucfirst($mois->copy()->addMonth()->translatedFormat('F')) }} →
        </a>
    </div>

    <div class="entetes-calendrier d-none d-md-grid mb-1">
        @foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $jour)
            <div class="text-muted small text-center">{{ $jour }}</div>
        @endforeach
    </div>

    <div class="grille-calendrier mb-4">
        @for ($jour = $debutGrille->copy(); $jour->lte($finGrille); $jour->addDay())
            @php
                $cle = $jour->toDateString();
                $duJour = $planifies->get($cle, collect());
                $souhaitsDuJour = $souhaits->get($cle, collect());
            @endphp
            <a class="case-jour @if (! $jour->isSameMonth($mois)) hors-mois @endif
                      @if ($jour->isToday()) aujourdhui @endif
                      @if ($jourChoisi && $jour->isSameDay($jourChoisi)) choisi @endif"
               href="{{ route('admin.rdv.calendrier', ['mois' => $mois->format('Y-m'), 'jour' => $cle]) }}">
                <span class="numero">{{ $jour->day }}</span>
                @foreach ($duJour as $rdv)
                    <span class="pastille-rdv statut-{{ $rdv->statut }}"
                          title="{{ $rdv->nom_contact }} — {{ $rdv->statutLisible() }}">
                        {{ $rdv->debut->format('G\\hi') }} {{ $rdv->nom_contact }}
                    </span>
                @endforeach
                @foreach ($souhaitsDuJour as $rdv)
                    <span class="pastille-rdv souhait" title="Demande en attente : {{ $rdv->nom_contact }}">
                        ? {{ $rdv->nom_contact }}
                    </span>
                @endforeach
            </a>
        @endfor
    </div>

    @if ($jourChoisi)
        @php
            $cle = $jourChoisi->toDateString();
            $detail = $planifies->get($cle, collect())->concat($souhaits->get($cle, collect()));
        @endphp
        <div class="card card-henne">
            <div class="card-body p-4">
                <h3 class="h6 text-uppercase text-muted mb-3 text-capitalize">
                    {{ $jourChoisi->translatedFormat('l d F Y') }}
                </h3>

                @if ($detail->isEmpty())
                    <p class="text-muted mb-0">Rien ce jour-là.</p>
                @else
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        @foreach ($detail as $rdv)
                            @php($conflits = $rdv->conflits())
                            <li class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge {{ $rdv->couleurBadge() }}">{{ $rdv->statutLisible() }}</span>
                                <strong>{{ $rdv->nom_contact }}</strong>
                                <span class="text-muted small">
                                    {{ $rdv->horaireAffiche() ?? 'Souhait : '.($rdv->souhaitAffiche() ?? '—') }}
                                </span>
                                @if ($conflits->isNotEmpty())
                                    <span class="badge text-bg-danger">⚠ Conflit avec {{ $conflits->pluck('nom_contact')->implode(', ') }}</span>
                                @endif
                                <a class="btn btn-outline-secondary btn-sm ms-auto"
                                   href="{{ route('admin.rdv.edit', $rdv) }}">Modifier</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif
@endsection
