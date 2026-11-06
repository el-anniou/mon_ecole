@extends('layouts.admin')

@section('title', 'Paramètres')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">Paramètres</h1>
        <p class="text-muted mb-0">
            Coordonnées, textes et liens du site. Tous les champs sont facultatifs :
            une information absente disparaît du site public au lieu d'afficher une donnée fictive.
        </p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            Certains champs sont invalides : corrigez les éléments signalés ci-dessous.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.parametres.update') }}" novalidate>
        @csrf
        @method('PUT')

        <div class="row g-4">
            @foreach ($groupes as $groupe => $champs)
                <div class="col-12 col-xl-6">
                    <div class="card card-henne h-100">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-4">{{ $groupe }}</h2>

                            @foreach ($champs as $cle => $champ)
                                <div class="mb-3">
                                    <label for="{{ $cle }}" class="form-label">{{ $champ['libelle'] }}</label>

                                    @if ($champ['type'] === 'textarea')
                                        <textarea name="{{ $cle }}" id="{{ $cle }}" rows="3"
                                                  class="form-control @error($cle) is-invalid @enderror">{{ old($cle, $valeurs[$cle] ?? '') }}</textarea>
                                    @else
                                        <input type="{{ $champ['type'] === 'tel' ? 'tel' : $champ['type'] }}"
                                               name="{{ $cle }}" id="{{ $cle }}"
                                               class="form-control @error($cle) is-invalid @enderror"
                                               value="{{ old($cle, $valeurs[$cle] ?? '') }}">
                                    @endif

                                    @error($cle)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    @if (! empty($champ['aide']))
                                        <div class="form-text">{{ $champ['aide'] }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn-rdv"><span>Enregistrer les paramètres</span></button>
        </div>
    </form>
@endsection
