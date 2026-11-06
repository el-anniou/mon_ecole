{{-- Carte publique d'une promotion active. --}}
<article class="carte-promotion h-100">
    <p class="surtitre mb-1">Offre du moment</p>
    <h3 class="h5 mb-2">{{ $promotion->titre }}</h3>
    <p class="mb-2">{{ $promotion->texte }}</p>
    @if ($periode = $promotion->periodeAffichee())
        <p class="text-muted small mb-2">{{ $periode }}</p>
    @endif
    @if ($promotion->lien_action)
        <a href="{{ $promotion->lien_action }}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener">
            En profiter
        </a>
    @endif
</article>
