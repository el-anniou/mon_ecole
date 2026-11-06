@extends('layouts.admin')

@section('title', 'Tableau de bord')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Tableau de bord</h1>
            <p class="text-muted mb-0">Bonjour {{ auth()->user()->name }} 👋</p>
        </div>
    </div>

    <div class="row g-3">
        @php
            // Modules : lien direct quand l'étape est livrée, jalon sinon.
            $modules = [
                ['Paramètres', 'Coordonnées, WhatsApp, textes du site', null, route('admin.parametres.edit')],
                ['Réalisations', 'Galerie, photos, publication, ordre', null, route('admin.realisations.index')],
                ['Catégories', 'Organisation et filtres de la galerie', null, route('admin.categories.index')],
                ['Prestations', 'Offres, tarifs, durées', null, route('admin.prestations.index')],
                ['Promotions', 'Offres à durée limitée', null, route('admin.promotions.index')],
                ['Témoignages', 'Autorisations et publication', null, route('admin.temoignages.index')],
                ['Rendez-vous', 'Demandes, calendrier, statuts', null, route('admin.rdv.index')],
                ['Clientes & fidélité', 'Fiches, carte de 5 visites', 'Étape 7', null],
                ['Messages', 'Formulaire de contact', 'Étape 8', null],
            ];
        @endphp

        @foreach ($modules as [$titre, $description, $etape, $lien])
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card card-henne h-100 @if (! $lien) opacity-75 @endif">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <h2 class="h6 mb-1">
                                @if ($lien)
                                    <a href="{{ $lien }}" class="stretched-link text-decoration-none">{{ $titre }}</a>
                                @else
                                    {{ $titre }}
                                @endif
                            </h2>
                            <span class="badge text-bg-dark border">{{ $etape ?? 'Disponible' }}</span>
                        </div>
                        <p class="text-muted small mb-0">{{ $description }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
