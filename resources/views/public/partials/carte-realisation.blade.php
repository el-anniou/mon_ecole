{{-- Carte d'une réalisation dans une grille publique. --}}
<a href="{{ route('public.realisation', $realisation->reference) }}"
   class="carte-realisation d-block text-decoration-none reveal" @isset($delai) style="--d: {{ $delai }}s" @endisset>
    @if ($url = $realisation->urlVignette())
        <img src="{{ $url }}" alt="{{ $realisation->texteAlternatif() }}"
             width="480" height="600" loading="lazy" decoding="async">
    @else
        <span class="carte-realisation-vide" aria-hidden="true">✦</span>
    @endif
    <span class="carte-realisation-legende">
        <span class="titre">{{ $realisation->titre ?: 'Voir le modèle' }}</span>
        <span class="reference">{{ $realisation->reference }}</span>
    </span>
</a>
