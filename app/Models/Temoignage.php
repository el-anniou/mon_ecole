<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Temoignage extends Model
{
    /** @use HasFactory<\Database\Factories\TemoignageFactory> */
    use HasFactory;

    protected $fillable = [
        'auteur',
        'contenu',
        'autorisation_affichage',
        'autorisation_notee_le',
        'autorisation_note',
        'publie',
    ];

    protected function casts(): array
    {
        return [
            'autorisation_affichage' => 'boolean',
            'autorisation_notee_le' => 'date',
            'publie' => 'boolean',
        ];
    }

    /**
     * Verrou CDC §8, imposé au niveau du modèle : quel que soit le chemin
     * (contrôleur, seeder, tinker…), un témoignage sans autorisation
     * enregistrée ne peut JAMAIS être marqué publié. Et dès qu'une
     * autorisation est accordée sans date, la date du jour est tracée.
     */
    protected static function booted(): void
    {
        static::saving(function (Temoignage $temoignage) {
            if (! $temoignage->autorisation_affichage) {
                $temoignage->publie = false;
            } elseif ($temoignage->autorisation_notee_le === null) {
                $temoignage->autorisation_notee_le = Carbon::today();
            }
        });
    }

    /**
     * Témoignages visibles sur le site public : publiés ET autorisés.
     * La double condition est volontairement redondante avec le verrou
     * du modèle (défense en profondeur).
     *
     * @param Builder<Temoignage> $query
     */
    public function scopePublies(Builder $query): void
    {
        $query->where('publie', true)->where('autorisation_affichage', true);
    }

    /**
     * Statut lisible pour l'administration.
     */
    public function statut(): string
    {
        return match (true) {
            ! $this->autorisation_affichage => 'Sans autorisation',
            ! $this->publie => 'Autorisé, non publié',
            default => 'Publié',
        };
    }

    /**
     * Trace du consentement, lisible (ex. « notée le 12/01/2026 — accord WhatsApp »).
     */
    public function autorisationAffichee(): ?string
    {
        if (! $this->autorisation_affichage) {
            return null;
        }

        $parties = [];

        if ($this->autorisation_notee_le) {
            $parties[] = 'notée le '.$this->autorisation_notee_le->format('d/m/Y');
        }

        if ($this->autorisation_note) {
            $parties[] = $this->autorisation_note;
        }

        return $parties === [] ? 'enregistrée' : implode(' — ', $parties);
    }
}
