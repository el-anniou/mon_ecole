<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#201613">
    <title>@yield('title', config('app.name'))</title>
    @stack('seo')
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100">
    @php($nomMarque = \App\Models\Parametre::valeur('nom_marque', config('app.name')))

    <header>
        <nav class="navbar navbar-expand-lg border-bottom" aria-label="Navigation principale">
            <div class="container">
                <a class="navbar-brand titre-marque" href="{{ route('public.home') }}">{{ $nomMarque }}</a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav-public"
                        aria-controls="nav-public" aria-expanded="false" aria-label="Ouvrir le menu">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="nav-public">
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                        <li class="nav-item">
                            <a class="nav-link @if (request()->routeIs('public.home')) active @endif"
                               href="{{ route('public.home') }}">Accueil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if (request()->routeIs('public.galerie') || request()->routeIs('public.realisation')) active @endif"
                               href="{{ route('public.galerie') }}">Réalisations</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if (request()->routeIs('public.prestations')) active @endif"
                               href="{{ route('public.prestations') }}">Prestations</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if (request()->routeIs('public.temoignages')) active @endif"
                               href="{{ route('public.temoignages') }}">Témoignages</a>
                        </li>
                        {{-- Contact rejoindra le menu à son étape (CDC §5). --}}
                        <li class="nav-item mt-2 mt-lg-0 ms-lg-2">
                            <a class="btn-rdv btn-rdv-nav" href="{{ route('public.rdv.create') }}">
                                <span>{{ \App\Models\Parametre::valeur('cta_texte', 'Prendre rendez-vous') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="flex-grow-1">
        @yield('content')
    </main>

    <footer class="py-4 mt-auto border-top">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 text-muted small">
                <span>{{ $nomMarque }}</span>
                <nav aria-label="Navigation secondaire" class="d-flex gap-3">
                    <a class="link-secondary" href="{{ route('public.home') }}">Accueil</a>
                    <a class="link-secondary" href="{{ route('public.galerie') }}">Réalisations</a>
                    <a class="link-secondary" href="{{ route('public.prestations') }}">Prestations</a>
                    <a class="link-secondary" href="{{ route('public.temoignages') }}">Témoignages</a>
                    <a class="link-secondary" href="{{ route('public.rdv.create') }}">Rendez-vous</a>
                    @if ($whatsappPied = \App\Models\Parametre::lienWhatsapp('Bonjour !'))
                        <a class="link-secondary" href="{{ $whatsappPied }}" target="_blank" rel="noopener">WhatsApp</a>
                    @endif
                </nav>
                <span>{{ \App\Models\Parametre::valeur('adresse_publique', 'Informations pratiques à venir') }}</span>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
