<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Realisation extends Model
{
    /** @use HasFactory<\Database\Factories\RealisationFactory> */
    use HasFactory;

    protected $fillable = [
        'reference', 'titre', 'description', 'texte_alternatif',
        'image_dossier', 'publiee', 'position', 'realisee_le',
    ];

    protected function casts(): array
    {
        return [
            'publiee' => 'boolean',
            'realisee_le' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $realisation) {
            $realisation->reference = $realisation->reference ?: static::prochaineReference();

            if ($realisation->position === 0 || $realisation->position === null) {
                $realisation->position = (int) static::query()->max('position') + 1;
            }
        });
    }

    /** @return BelongsToMany<Categorie, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Categorie::class, 'categorie_realisation');
    }

    /** @param Builder<Realisation> $query */
    public function scopePubliees(Builder $query): void
    {
        $query->where('publiee', true);
    }

    /** @param Builder<Realisation> $query */
    public function scopeOrdonnees(Builder $query): void
    {
        $query->orderBy('position')->orderByDesc('id');
    }

    /**
     * Référence stable et lisible : R-0001, R-0002…
     * Utilisée dans les liens publics et le message WhatsApp (CDC §6).
     */
    public static function prochaineReference(): string
    {
        $prochainId = (int) static::query()->max('id') + 1;

        $reference = sprintf('R-%04d', $prochainId);

        while (static::query()->where('reference', $reference)->exists()) {
            $reference = sprintf('R-%04d', ++$prochainId);
        }

        return $reference;
    }

    /**
     * Nom alternatif de l'image : champ dédié, sinon titre, sinon libellé générique.
     */
    public function texteAlternatif(): string
    {
        return $this->texte_alternatif ?: ($this->titre ?: 'Réalisation de henné '.$this->reference);
    }

    public function urlVignette(): ?string
    {
        return $this->urlVariante('vignette');
    }

    public function urlGrande(): ?string
    {
        return $this->urlVariante('grande');
    }

    protected function urlVariante(string $variante): ?string
    {
        $disque = Storage::disk('public');

        foreach (["{$this->image_dossier}/{$variante}.webp", "{$this->image_dossier}/{$variante}.jpg"] as $chemin) {
            if ($disque->exists($chemin)) {
                return $disque->url($chemin);
            }
        }

        return null;
    }
}
