@extends('layouts.public')

@section('title', 'Réalisations — '.\App\Models\Parametre::valeur('nom_marque', config('app.name')))

@section('content')
    <section class="container py-5">
        <header class="text-center mb-4">
            <h1 class="section-titre d-inline-block">Les réalisations</h1>
            <p class="text-muted">Parcourez les créations et trouvez le modèle qui vous ressemble.</p>
        </header>

        @if ($categories->isNotEmpty())
            <nav class="d-flex flex-wrap justify-content-center gap-2 mb-4" aria-label="Filtrer par catégorie">
                <a href="{{ route('public.galerie') }}"
                   class="pilule text-decoration-none @unless ($categorieActive) actif @endunless"
                   @unless ($categorieActive) aria-current="true" @endunless>Toutes</a>
                @foreach ($categories as $categorie)
                    <a href="{{ route('public.galerie', ['categorie' => $categorie->slug]) }}"
                       class="pilule text-decoration-none @if ($categorieActive?->is($categorie)) actif @endif"
                       @if ($categorieActive?->is($categorie)) aria-current="true" @endif>{{ $categorie->nom }}</a>
                @endforeach
            </nav>
        @endif

        @if ($realisations->isEmpty())
            <div class="text-center text-muted py-5">
                <p class="display-6" aria-hidden="true">✦</p>
                @if ($categorieActive)
                    <p>Aucune réalisation dans « {{ $categorieActive->nom }} » pour le moment.</p>
                    <a href="{{ route('public.galerie') }}" class="btn btn-outline-secondary">Voir toutes les réalisations</a>
                @else
                    <p>La galerie se remplit bientôt : revenez très vite !</p>
                @endif
            </div>
        @else
            <div class="grille-galerie">
                @foreach ($realisations as $realisation)
                    @include('public.partials.carte-realisation', [
                        'realisation' => $realisation,
                        'delai' => ($loop->index % 4) * 0.06,
                    ])
                @endforeach
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $realisations->links() }}
            </div>
        @endif
    </section>
@endsection
