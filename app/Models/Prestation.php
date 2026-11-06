<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestation extends Model
{
    /** @use HasFactory<\Database\Factories\PrestationFactory> */
    use HasFactory;

    /**
     * Types de prix (CDC §7) et libellés d'administration.
     *
     * @var array<string, string>
     */
    public const TYPES_PRIX = [
        'fixe' => 'Prix fixe',
        'a_partir_de' => 'À partir de',
        'fourchette' => 'Fourchette de prix',
        'sur_demande' => 'Prix sur demande',
        'non_affiche' => 'Prix non affiché',
    ];

    protected $fillable = [
        'nom', 'description', 'type_prix', 'prix_min', 'prix_max',
        'duree_minutes', 'position', 'publiee',
    ];

    protected function casts(): array
    {
        return [
            'publiee' => 'boolean',
            'prix_min' => 'decimal:2',
            'prix_max' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $prestation) {
            if ($prestation->position === 0 || $prestation->position === null) {
                $prestation->position = (int) static::query()->max('position') + 1;
            }
        });
    }

    /** @param Builder<Prestation> $query */
    public function scopePubliees(Builder $query): void
    {
        $query->where('publiee', true);
    }

    /** @param Builder<Prestation> $query */
    public function scopeOrdonnees(Builder $query): void
    {
        $query->orderBy('position')->orderBy('nom');
    }

    /**
     * Libellé public du tarif selon le type de prix, avec la devise
     * administrée. Retourne null si le prix n'est pas affiché.
     */
    public function prixAffiche(): ?string
    {
        $devise = Parametre::valeur('devise');

        return match ($this->type_prix) {
            'fixe' => $this->montant($this->prix_min, $devise),
            'a_partir_de' => 'À partir de '.$this->montant($this->prix_min, $devise),
            'fourchette' => $this->montant($this->prix_min, $devise).' – '.$this->montant($this->prix_max, $devise),
            'sur_demande' => 'Prix sur demande',
            default => null,
        };
    }

    /**
     * Durée indicative lisible (ex. « 1 h 30 »), ou null.
     */
    public function dureeAffichee(): ?string
    {
        if (! $this->duree_minutes) {
            return null;
        }

        $heures = intdiv($this->duree_minutes, 60);
        $minutes = $this->duree_minutes % 60;

        return trim(($heures ? $heures.' h' : '').($minutes ? ' '.sprintf('%02d', $minutes) : ($heures ? '' : $minutes.' min')));
    }

    protected function montant(?string $valeur, ?string $devise): string
    {
        $nombre = (float) $valeur;

        $texte = number_format($nombre, fmod($nombre, 1) ? 2 : 0, ',', ' ');

        return $devise ? $texte.' '.$devise : $texte;
    }
}
