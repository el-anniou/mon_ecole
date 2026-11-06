@extends('layouts.public')

@php
    $titrePage = $realisation->titre ?: 'Modèle '.$realisation->reference;
    $lienModele = route('public.realisation', $realisation->reference);
    $messageWhatsapp = "Bonjour ! Je veux ce modèle : {$titrePage} ({$realisation->reference}) {$lienModele}";
    $lienWhatsapp = \App\Models\Parametre::lienWhatsapp($messageWhatsapp);
@endphp

@section('title', $titrePage.' — '.\App\Models\Parametre::valeur('nom_marque', config('app.name')))

@section('content')
    <section class="container py-5">
        <nav aria-label="Fil d'Ariane" class="mb-4 small">
            <a href="{{ route('public.galerie') }}" class="link-secondary">← Retour aux réalisations</a>
        </nav>

        <div class="row g-5 align-items-start">
            <div class="col-12 col-lg-7">
                @if ($url = $realisation->urlGrande())
                    <button type="button" class="vue-modele reveal" data-visionneuse
                            aria-label="Agrandir l'image">
                        <img src="{{ $url }}" alt="{{ $realisation->texteAlternatif() }}"
                             width="1280" height="1600" fetchpriority="high" decoding="async">
                    </button>
                @else
                    <div class="vue-modele vue-modele-vide reveal" aria-hidden="true">✦</div>
                @endif
            </div>

            <div class="col-12 col-lg-5 reveal" style="--d: .1s">
                <p class="surtitre mb-1">Référence {{ $realisation->reference }}</p>
                <h1>{{ $titrePage }}</h1>

                @if ($realisation->description)
                    <p class="texte-hero lead">{{ $realisation->description }}</p>
                @endif

                @if ($realisation->categories->isNotEmpty())
                    <ul class="list-unstyled d-flex flex-wrap gap-2 my-3">
                        @foreach ($realisation->categories as $categorie)
                            <li>
                                <a class="pastille text-decoration-none"
                                   href="{{ route('public.galerie', ['categorie' => $categorie->slug]) }}">
                                    {{ $categorie->nom }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="d-flex flex-wrap gap-3 mt-4">
                    @if ($lienWhatsapp)
                        <a class="btn-rdv pulse" href="{{ $lienWhatsapp }}" target="_blank" rel="noopener">
                            <span>Je veux ce modèle</span><span class="fleche" aria-hidden="true">→</span>
                        </a>
                    @endif
                    <a class="btn btn-outline-secondary btn-lg"
                       href="{{ route('public.rdv.create', ['realisation' => $realisation->id]) }}">
                        Demander un rendez-vous
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-lg" data-partage
                            data-titre="{{ $titrePage }}" data-url="{{ $lienModele }}">
                        Partager
                    </button>
                </div>
                @if ($lienWhatsapp)
                    <p class="text-muted small mt-3 mb-0">
                        WhatsApp s'ouvre avec un message prérempli que vous pouvez modifier avant envoi.
                    </p>
                @endif
            </div>
        </div>
    </section>

    {{-- Visionneuse plein écran accessible --}}
    @if ($realisation->urlGrande())
        <dialog class="visionneuse" data-visionneuse-boite>
            <button type="button" class="visionneuse-fermer" data-visionneuse-fermer aria-label="Fermer la visionneuse">✕</button>
            <img src="{{ $realisation->urlGrande() }}" alt="{{ $realisation->texteAlternatif() }}" loading="lazy">
        </dialog>
    @endif
@endsection

@push('scripts')
    @vite('resources/js/galerie.js')
@endpush
