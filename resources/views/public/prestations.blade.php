@extends('layouts.public')

@php($whatsapp = \App\Models\Parametre::lienWhatsapp('Bonjour, j\'aimerais des informations sur vos prestations.'))

@section('title', 'Prestations — '.\App\Models\Parametre::valeur('nom_marque', config('app.name')))

@section('content')
    <section class="container py-5">
        <header class="text-center mb-5">
            <h1 class="section-titre d-inline-block">Les prestations</h1>
            <p class="text-muted">Des formules adaptées à votre occasion.</p>
        </header>

        @if ($promotions->isNotEmpty())
            <div class="row g-4 justify-content-center mb-5" id="offres">
                @foreach ($promotions as $promotion)
                    <div class="col-md-6 col-lg-4 reveal" style="--d: {{ ($loop->index % 3) * 0.1 }}s">
                        @include('public.partials.carte-promotion')
                    </div>
                @endforeach
            </div>
        @endif

        @if ($prestations->isEmpty())
            <div class="text-center text-muted py-5">
                <p class="display-6" aria-hidden="true">✦</p>
                <p>Les prestations seront bientôt détaillées ici.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach ($prestations as $prestation)
                    <div class="col-12 col-md-6 col-xl-4 reveal" style="--d: {{ ($loop->index % 3) * 0.08 }}s">
                        <div class="card card-henne h-100">
                            <div class="card-body p-4 d-flex flex-column">
                                <h2 class="h5">{{ $prestation->nom }}</h2>
                                @if ($prestation->description)
                                    <p class="text-muted">{{ $prestation->description }}</p>
                                @endif

                                <div class="mt-auto pt-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    @if ($prix = $prestation->prixAffiche())
                                        <span class="prix-prestation">{{ $prix }}</span>
                                    @else
                                        <span></span>
                                    @endif
                                    @if ($duree = $prestation->dureeAffichee())
                                        <span class="pastille">{{ $duree }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($prestations->contains('type_prix', 'sur_demande'))
                <p class="text-center text-muted small mt-4">
                    Les prestations « sur demande » sont chiffrées selon le motif et la durée : contactez-moi pour en discuter.
                </p>
            @endif
        @endif

        <div class="text-center mt-5 reveal d-flex flex-wrap justify-content-center align-items-center gap-3">
            <a class="btn-rdv pulse" href="{{ route('public.rdv.create') }}">
                <span>Demander un rendez-vous</span><span class="fleche" aria-hidden="true">→</span>
            </a>
            @if ($whatsapp)
                <a class="link-secondary" href="{{ $whatsapp }}" target="_blank" rel="noopener">
                    ou discutons-en sur WhatsApp
                </a>
            @endif
        </div>
    </section>
@endsection
