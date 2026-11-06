<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#201613">
    <title>Connexion — {{ config('app.name') }}</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    <main class="min-vh-100 d-flex align-items-center justify-content-center p-3">
        <div class="carte-connexion w-100">
            <div class="text-center mb-4">
                <p class="surtitre mb-1">{{ config('app.name') }}</p>
                <h1 class="h3">Espace d'administration</h1>
            </div>

            <div class="card card-henne">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.login.store') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse e-mail</label>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required autofocus autocomplete="username">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required autocomplete="current-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-4">
                            <input type="checkbox" name="se_souvenir" id="se_souvenir" value="1" class="form-check-input">
                            <label for="se_souvenir" class="form-check-label">Rester connectée</label>
                        </div>

                        <button type="submit" class="btn-rdv w-100 justify-content-center">
                            <span>Se connecter</span>
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-center text-muted small mt-4 mb-0">
                <a href="{{ route('public.home') }}">← Retour au site</a>
            </p>
        </div>
    </main>
</body>
</html>
