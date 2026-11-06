@extends('layouts.public')

@php
    use App\Models\Parametre;
    use App\Models\RendezVous;

    $whatsapp = Parametre::lienWhatsapp('Bonjour, je souhaite prendre rendez-vous.');
@endphp

@section('title', 'Demande de rendez-vous — '.Parametre::valeur('nom_marque', 'HENNÉ MUSLIM'))

@section('content')
    <section class="container py-5">
        <div class="text-center mb-5">
            <p class="surtitre reveal">Sans compte, sans engagement</p>
            <h1 class="section-titre reveal" style="--d:.08s">Demande de rendez-vous</h1>
            <p class="text-muted mx-auto reveal" style="--d:.16s; max-width: 560px;">
                Dites-moi ce qui vous ferait plaisir et quand vous seriez disponible —
                je vous réponds personnellement pour fixer ensemble le rendez-vous.
            </p>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-7">
                @if (session('demande_envoyee'))
                    <div class="alert alert-success" role="status">
                        <strong>Votre demande est bien envoyée, merci !</strong><br>
                        Ce message ne vaut pas confirmation : je reviens vers vous
                        personnellement, par le moyen de contact que vous avez laissé,
                        pour convenir du rendez-vous.
                    </div>
                @endif

                <form method="POST" action="{{ route('public.rdv.store') }}" class="card card-henne reveal">
                    @csrf
                    <div class="card-body p-4 p-md-5">

                        @if ($realisation)
                            <div class="alert alert-secondary py-2 small mb-4">
                                Votre demande concerne le modèle
                                <a href="{{ route('public.realisation', $realisation) }}">« {{ $realisation->titre }} » ({{ $realisation->reference }})</a>.
                            </div>
                            <input type="hidden" name="realisation_id" value="{{ $realisation->id }}">
                        @endif

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="nom_contact" class="form-label">Votre nom ou prénom <span class="text-danger">*</span></label>
                                <input type="text" name="nom_contact" id="nom_contact" required maxlength="100"
                                       class="form-control @error('nom_contact') is-invalid @enderror"
                                       value="{{ old('nom_contact') }}" autocomplete="name">
                                @error('nom_contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="moyen_contact" class="form-label">Comment vous joindre <span class="text-danger">*</span></label>
                                <input type="text" name="moyen_contact" id="moyen_contact" required maxlength="150"
                                       class="form-control @error('moyen_contact') is-invalid @enderror"
                                       value="{{ old('moyen_contact') }}"
                                       placeholder="Téléphone, WhatsApp ou e-mail">
                                @error('moyen_contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        @if ($prestations->isNotEmpty())
                            <div class="mb-3">
                                <label for="prestation_id" class="form-label">Prestation envisagée <span class="text-muted">(facultatif)</span></label>
                                <select name="prestation_id" id="prestation_id"
                                        class="form-select @error('prestation_id') is-invalid @enderror">
                                    <option value="">— Je ne sais pas encore —</option>
                                    @foreach ($prestations as $prestation)
                                        <option value="{{ $prestation->id }}"
                                            @selected((int) old('prestation_id', $prestationChoisie) === $prestation->id)>
                                            {{ $prestation->nom }}@if ($prix = $prestation->prixAffiche()) — {{ $prix }}@endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('prestation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endif

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="date_souhaitee" class="form-label">Date souhaitée <span class="text-muted">(facultative)</span></label>
                                <input type="date" name="date_souhaitee" id="date_souhaitee"
                                       min="{{ now()->toDateString() }}"
                                       class="form-control @error('date_souhaitee') is-invalid @enderror"
                                       value="{{ old('date_souhaitee') }}">
                                @error('date_souhaitee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <span class="form-label d-block">Moment préféré <span class="text-muted">(facultatif)</span></span>
                                <div class="d-flex flex-wrap gap-2 pt-1">
                                    @foreach (RendezVous::PERIODES as $valeur => $libelle)
                                        <input type="radio" class="btn-check" name="periode_souhaitee"
                                               id="periode-{{ $valeur }}" value="{{ $valeur }}"
                                               @checked(old('periode_souhaitee') === $valeur)>
                                        <label class="pilule" for="periode-{{ $valeur }}">{{ $libelle }}</label>
                                    @endforeach
                                </div>
                                @error('periode_souhaitee')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="message" class="form-label">Votre message <span class="text-muted">(facultatif)</span></label>
                            <textarea name="message" id="message" rows="4" maxlength="1500"
                                      class="form-control @error('message') is-invalid @enderror"
                                      placeholder="Occasion, motif rêvé, nombre de personnes, questions…">{{ old('message') }}</textarea>
                            @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Piège à robots : invisible pour un humain, à laisser vide. --}}
                        <div style="position:absolute; left:-9999px;" aria-hidden="true">
                            <label for="site_web">Ne pas remplir</label>
                            <input type="text" name="site_web" id="site_web" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <button type="submit" class="btn-rdv pulse"><span>Envoyer ma demande</span></button>
                            @if ($whatsapp)
                                <a class="link-secondary" href="{{ $whatsapp }}" target="_blank" rel="noopener">
                                    ou écrivez-moi directement sur WhatsApp
                                </a>
                            @endif
                        </div>

                        <p class="text-muted small mt-4 mb-0">
                            Aucune confirmation automatique : chaque demande reçoit une réponse
                            personnelle. Vos coordonnées ne servent qu'à vous répondre.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
