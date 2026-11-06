@extends('layouts.public')

@section('title', 'Témoignages — '.\App\Models\Parametre::valeur('nom_marque', 'HENNÉ MUSLIM'))

@section('content')
    <section class="container py-5">
        <div class="text-center mb-5">
            <p class="surtitre reveal">Elles en parlent</p>
            <h1 class="section-titre reveal" style="--d:.08s">Témoignages</h1>
            <p class="text-muted mx-auto reveal" style="--d:.16s; max-width: 560px;">
                Chaque témoignage est affiché avec l'accord explicite de sa cliente.
            </p>
        </div>

        @if ($temoignages->isEmpty())
            <p class="text-center text-muted">Les premiers témoignages arrivent bientôt.</p>
        @else
            <div class="row g-4 justify-content-center">
                @foreach ($temoignages as $temoignage)
                    <div class="col-12 col-md-6 col-lg-4 reveal" style="--d:{{ ($loop->index % 3) * 0.08 }}s">
                        @include('public.partials.carte-temoignage')
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection
