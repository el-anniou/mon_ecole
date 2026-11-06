<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#201613">
    <title>@yield('title', 'Administration') — {{ config('app.name') }}</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg border-bottom" aria-label="Navigation d'administration">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand titre-marque" href="{{ route('admin.dashboard') }}">
                {{ config('app.name') }} <span class="badge-admin">admin</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav-admin"
                    aria-controls="nav-admin" aria-expanded="false" aria-label="Ouvrir le menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="nav-admin">
                <ul class="navbar-nav me-auto mt-2 mt-lg-0">
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('admin.dashboard')) active @endif"
                           href="{{ route('admin.dashboard') }}">Tableau de bord</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('admin.rdv.*')) active @endif"
                           href="{{ route('admin.rdv.index') }}">Rendez-vous</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('admin.realisations.*')) active @endif"
                           href="{{ route('admin.realisations.index') }}">Réalisations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('admin.categories.*')) active @endif"
                           href="{{ route('admin.categories.index') }}">Catégories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('admin.prestations.*')) active @endif"
                           href="{{ route('admin.prestations.index') }}">Prestations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('admin.promotions.*')) active @endif"
                           href="{{ route('admin.promotions.index') }}">Promotions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('admin.temoignages.*')) active @endif"
                           href="{{ route('admin.temoignages.index') }}">Témoignages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('admin.parametres.*')) active @endif"
                           href="{{ route('admin.parametres.edit') }}">Paramètres</a>
                    </li>
                    {{-- Les entrées Rendez-vous, Clientes…
                         apparaîtront au fil des étapes de construction. --}}
                </ul>

                <div class="d-flex align-items-center gap-3 pb-2 pb-lg-0">
                    <a class="nav-link p-0 small" href="{{ route('public.home') }}" target="_blank" rel="noopener">
                        Voir le site ↗
                    </a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Se déconnecter</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1 py-4">
        <div class="container-fluid px-3 px-lg-4">
            @if (session('succes'))
                <div class="alert alert-success" role="status">{{ session('succes') }}</div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="py-3 border-top">
        <div class="container-fluid px-3 px-lg-4 text-muted small">
            Espace privé — réservé à l'administration.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
