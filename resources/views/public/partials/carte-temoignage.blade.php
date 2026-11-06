{{-- Carte témoignage — attend $temoignage (publié + autorisé uniquement). --}}
<figure class="carte-temoignage card-henne h-100 p-4 mb-0">
    <blockquote class="mb-3">{{ $temoignage->contenu }}</blockquote>
    <figcaption class="auteur">— {{ $temoignage->auteur }}</figcaption>
</figure>
