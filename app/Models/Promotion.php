<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Promotion extends Model
{
    /** @use HasFactory<\Database\Factories\PromotionFactory> */
    use HasFactory;

    protected $fillable = ['titre', 'texte', 'debut', 'fin', 'lien_action', 'publiee'];

    protected function casts(): array
    {
        return [
            'publiee' => 'boolean',
            'debut' => 'date',
            'fin' => 'date',
        ];
    }

    /**
     * Verrou CDC §7 : une promotion n'est publique que si elle est publiée
     * ET comprise dans sa période de validité. Expirée ou désactivée,
     * elle disparaît automatiquement du site.
     *
     * @param Builder<Promotion> $query
     */
    public function scopeActives(Builder $query): void
    {
        $aujourdhui = Carbon::today();

        $query->where('publiee', true)
            ->where(fn ($q) => $q->whereNull('debut')->orWhereDate('debut', '<=', $aujourdhui))
            ->where(fn ($q) => $q->whereNull('fin')->orWhereDate('fin', '>=', $aujourdhui));
    }

    /**
     * Statut lisible pour l'administration.
     */
    public function statut(): string
    {
        if (! $this->publiee) {
            return 'Dépubliée';
        }

        $aujourdhui = Carbon::today();

        if ($this->debut && $this->debut->isAfter($aujourdhui)) {
            return 'À venir';
        }

        if ($this->fin && $this->fin->isBefore($aujourdhui)) {
            return 'Expirée';
        }

        return 'En ligne';
    }

    /**
     * Période lisible (ex. « du 01/02 au 15/02/2026 »).
     */
    public function periodeAffichee(): ?string
    {
        return match (true) {
            $this->debut && $this->fin => 'Du '.$this->debut->format('d/m/Y').' au '.$this->fin->format('d/m/Y'),
            (bool) $this->fin => 'Jusqu\'au '.$this->fin->format('d/m/Y'),
            (bool) $this->debut => 'À partir du '.$this->debut->format('d/m/Y'),
            default => null,
        };
    }
}
