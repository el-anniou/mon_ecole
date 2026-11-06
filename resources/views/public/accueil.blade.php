{{--
    Page d'accueil — adaptée au design B « Élégance nuit » (décision n°10).

    IMPORTANT : ce template suppose un code applicatif (modèles Parametre,
    Telephone, routes rdv.create / galerie / prestations / temoignages,
    partials rail / lightbox / chips-ligne / avis-modal / promotion-card /
    facebook-feed, scripts galerie.js / accueil.js / vitrine.js) qui n'est
    PAS ENCORE dans ce dépôt. Il n'est donc pas routé tant que ce code
    n'a pas été importé.

    Adaptations design B par rapport à la version d'origine :
      1. SVG du cadre vide : stroke="currentColor" au lieu de la couleur
         claire codée en dur (#8a4b2f), pour hériter du cuivre du thème.
      2. Tout le reste du design (fond nuit, ivoire, cuivre, or) est porté
         par resources/scss/app.scss — le balisage reste inchangé, ce qui
         simplifie la maintenance.
--}}
@extends('layouts.public')

@push('seo')
    @php
        $numero  = \App\Support\Telephone::international(\App\Models\Parametre::valeur('telephone'));
        $image   = \App\Models\Parametre::valeur('og_image_path') ?: \App\Models\Parametre::valeur('logo_path');
        $reseaux = array_values(array_filter([
            \App\Models\Parametre::valeur('facebook_url'),
            \App\Models\Parametre::valeur('instagram_url'),
        ]));

        $donnees = array_filter([
            '@context'    => 'https://schema.org',
            '@type'       => 'LocalBusiness',
            'name'        => \App\Models\Parametre::valeur('nom_marque', config('app.name')),
            'description' => \App\Models\Parametre::valeur('presentation') ?: \App\Models\Parametre::valeur('slogan'),
            'url'         => route('home'),
            'image'       => $image ? asset('storage/' . $image) : null,
            'telephone'   => $numero ? '+' . $numero : null,
            'email'       => \App\Models\Parametre::valeur('email'),
            'sameAs'      => $reseaux ?: null,
        ]);

        if (config('seo.ville')) {
            $donnees['address'] = [
                '@type'           => 'PostalAddress',
                'addressLocality' => config('seo.ville'),
                'addressCountry'  => config('seo.pays'),
            ];
        }
    @endphp
    <script type="application/ld+json">{!! json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush

@php
    use App\Models\Parametre;

    $nomMarque = Parametre::valeur('nom_marque', config('app.name'));
    $heroTitre = Parametre::valeur('hero_titre') ?: $nomMarque;
    $heroTexte = Parametre::valeur('hero_texte');
    $ctaTexte  = Parametre::valeur('cta_texte') ?: 'Prendre rendez-vous';
    $whatsapp  = Parametre::lienWhatsapp('Bonjour, je souhaite prendre rendez-vous.');
    $photos    = $groupes['*']->take(3)->values();
@endphp

@section('title', $nomMarque . ' – Tatouage au henné')

@section('content')

    {{-- 1. Hero (inchangé) --}}
    <section class="hero-accueil" id="accueil">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 reveal">
                    <p class="surtitre">{{ $nomMarque }} · Henné sur rendez-vous</p>
                    <h1>{{ $heroTitre }}</h1>
                    @if ($heroTexte)
                        <p class="lead texte-hero">{{ $heroTexte }}</p>
                    @endif

                    <div class="d-flex flex-wrap align-items-center gap-3 mt-4">
                        <a class="btn-rdv pulse" href="{{ route('rdv.create') }}">
                            <span>{{ $ctaTexte }}</span><span class="fleche" aria-hidden="true">→</span>
                        </a>
                       <a class="btn btn-outline-secondary btn-lg" href="#realisations">Voir les réalisations</a>
                    </div>

                    <ul class="list-unstyled d-flex flex-wrap gap-2 mt-4 mb-0">
                        <li class="pastille">✦ Motifs sur mesure</li>
                        <li class="pastille">✦ Réponse par WhatsApp</li>
                        <li class="pastille">✦ Sur rendez-vous</li>
                    </ul>
                </div>

                <div class="col-lg-6 reveal" style="--d: .15s">
                    <div class="collage">
                        @forelse ($photos as $i => $photo)
                            <div class="cadre c{{ $i + 1 }}">
                                <img src="{{ $photo->image_url }}" alt="{{ $photo->description ?: 'Réalisation de henné' }}"
                                     width="400" height="400"
                                     @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif decoding="async">
                            </div>
                        @empty
                            <div class="cadre c1 cadre-vide">
                                <svg viewBox="0 0 120 120" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                    <circle cx="60" cy="60" r="8"/>
                                    <path d="M60 52 C48 32 28 40 34 56 M60 52 C72 32 92 40 86 56 M60 68 C48 88 28 80 34 64 M60 68 C72 88 92 80 86 64"/>
                                    <path d="M8 60 H34 M86 60 H112"/>
                                </svg>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. Offres du moment (section absente s'il n'y en a pas) --}}
    @if ($promotions->isNotEmpty())
        <section class="container py-5" id="offres">
            <h2 class="section-titre text-center mb-5 reveal">Offres du moment</h2>
            <div class="row g-4 justify-content-center eventail">
                @foreach ($promotions as $promotion)
                    <div class="col-md-6 col-lg-4 reveal" style="--d: {{ ($loop->index % 3) * 0.1 }}s">
                        @include('partials.promotion-card')
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 3. Réalisations : anneau tournant, 5 photos par catégorie --}}
    @if ($groupes['*']->isNotEmpty())
        <section class="container py-5" id="realisations">
            <h2 class="section-titre text-center mb-4 reveal">Mes réalisations</h2>

            @if ($categories->isNotEmpty())
                <div class="d-flex flex-wrap justify-content-center gap-2 mb-4" role="group" aria-label="Filtrer par catégorie">
                    <button type="button" class="pilule actif" data-filtre="*" aria-pressed="true"
                            data-href="{{ route('galerie') }}" data-libelle="Voir toute la galerie">Toutes</button>
                    @foreach ($categories as $categorie)
                        <button type="button" class="pilule" data-filtre="{{ $categorie->slug }}" aria-pressed="false"
                                data-href="{{ route('galerie', ['categorie' => $categorie->slug]) }}"
                                data-libelle="Voir tout : {{ $categorie->nom }}">{{ $categorie->nom }}</button>
                    @endforeach
                </div>
            @endif

            <div id="galerie-grille">
                @foreach ($groupes as $cle => $liste)
                    <div data-groupe="{{ $cle }}" @unless ($loop->first) hidden @endunless>
                        @include('partials.rail', [
                            'liste'     => $liste,
                            'type'      => 'realisation',
                            'etiquette' => $cle === '*' ? 'Réalisations' : 'Réalisations : ' . $categories->firstWhere('slug', $cle)?->nom,
                        ])
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-4 reveal">
                <a id="voir-tout-realisations" href="{{ route('galerie') }}" class="btn btn-outline-secondary btn-lg">
                    <span class="libelle">Voir toute la galerie</span>
                </a>
            </div>
        </section>
        @include('partials.lightbox')
    @endif

    {{-- 4. Prestations : anneau tournant --}}
@if ($prestations->isNotEmpty())
    <section class="container py-5" id="prestations">
        <h2 class="section-titre text-center mb-4 reveal">Mes prestations</h2>

       @include('partials.rail', [
            'liste'     => $prestations,
            'type'      => 'prestation',
            'etiquette' => 'Prestations',
        ])

        @if ($prestations->contains('type_prix', 'sur_devis'))
            <p class="text-center text-muted small mt-3">
                Les prestations « sur devis » sont chiffrées selon le motif et la durée : contactez-moi pour en discuter.
            </p>
        @endif
        <div class="text-center mt-4 reveal">
            <a href="{{ route('prestations') }}" class="btn btn-outline-secondary btn-lg">Voir toutes les prestations</a>
        </div>
    </section>
@endif

    {{-- 5. Pourquoi choisir ce henné ? (textes provisoires, à remplacer par ceux de la cliente) --}}
    <section class="container py-5" id="pourquoi">
        <h2 class="section-titre text-center mb-5 reveal">Pourquoi choisir ce henné ?</h2>
        <div class="row g-4 eventail">
            @php
                $atouts = [
                    ['Un travail soigné', 'Des motifs dessinés avec précision, adaptés à votre style et à votre occasion.',
                     '<path d="M12 3l2.4 5.2 5.6.7-4.1 3.9 1 5.6L12 15.7 7.1 18.4l1-5.6L4 8.9l5.6-.7z"/>'],
                    ['Un henné de qualité', 'Une pâte préparée avec soin, pour une couleur riche et un résultat qui dure.',
                     '<path d="M12 21c-4.4-1.2-7-4.7-7-9 0-3 2-6.2 7-9 5 2.8 7 6 7 9 0 4.3-2.6 7.8-7 9z"/><path d="M12 21V9"/>'],
                    ['Un accompagnement simple', 'Un contact direct par WhatsApp, et une réponse rapide pour votre rendez-vous.',
                     '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.7A8 8 0 1 1 21 12z"/><path d="M9 11h6M9 14h4"/>'],
                ];
            @endphp
            @foreach ($atouts as [$titre, $texte, $icone])
                <div class="col-md-4 reveal" style="--d: {{ $loop->index * 0.1 }}s">
                    <div class="card card-henne h-100 text-center">
                        <div class="card-body p-4">
                            <div class="icone-atout" aria-hidden="true">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $icone !!}</svg>
                            </div>
                            <h3 class="h5">{{ $titre }}</h3>
                            <p class="text-muted mb-0">{{ $texte }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>


    {{-- 6. Témoignages : pastilles défilantes sur deux lignes, en sens contraires --}}
    @if ($temoignages->isNotEmpty())
        @php
            $liste  = $temoignages->values();
            $lignes = $liste->count() >= 4
                ? [
                    $liste->filter(fn ($t, $i) => $i % 2 === 0)->values(),
                    $liste->filter(fn ($t, $i) => $i % 2 === 1)->values(),
                ]
                : [$liste];
        @endphp

        <section class="py-5" id="temoignages">
            <div class="container">
                <h2 class="section-titre text-center mb-5 reveal">Elles m'ont fait confiance</h2>
            </div>

            <div class="chips reveal">
                @foreach ($lignes as $i => $ligne)
                    @include('partials.chips-ligne', [
                        'liste'   => $ligne,
                        'inverse' => $i === 1,
                        'boucle'  => count($lignes) === 2,
                        'vitesse' => $i === 1 ? 30 : 40,
                    ])
                @endforeach
            </div>

            <div class="container">
                <div class="d-flex flex-wrap justify-content-center gap-3 mt-4 reveal">
                    <a href="{{ route('temoignages.create') }}" class="btn btn-outline-secondary btn-lg">Laisser un avis</a>
                    <a href="{{ route('temoignages') }}" class="btn btn-outline-secondary btn-lg">Lire tous les témoignages</a>
                </div>
            </div>
        </section>
        @include('partials.avis-modal')
    @endif

    {{-- Fil Facebook : en suspens, en attente de la page publique --}}
    @include('partials.facebook-feed')

    {{-- 7. Appel à l'action final --}}
    <section class="container py-5">
        <div class="bandeau-cta text-center reveal">
            <h2>Envie d'un henné qui vous ressemble ?</h2>
            <p class="mb-4">Choisissez votre date, je vous confirme rapidement.</p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a class="btn-rdv pulse" href="{{ route('rdv.create') }}">
                    <span>{{ $ctaTexte }}</span><span class="fleche" aria-hidden="true">→</span>
                </a>
                @if ($whatsapp)
                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn btn-outline-light btn-lg rounded-pill">
                        Écrire sur WhatsApp
                    </a>
                @endif

            </div>
        </div>
    </section>
    {{-- Bouton flottant rendez-vous (mobile) --}}
    <a id="cta-flottant" class="btn-rdv pulse cta-flottant" href="{{ route('rdv.create') }}">
        <span>{{ $ctaTexte }}</span><span class="fleche" aria-hidden="true">→</span>
    </a>
@endsection

@push('scripts')
    @vite(['resources/js/galerie.js', 'resources/js/accueil.js', 'resources/js/vitrine.js'])
@endpush
