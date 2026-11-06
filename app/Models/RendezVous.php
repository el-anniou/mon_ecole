<?php

namespace App\Models;

use App\Support\Telephone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RendezVous extends Model
{
    /** @use HasFactory<\Database\Factories\RendezVousFactory> */
    use HasFactory;

    protected $table = 'rendez_vous';

    /** Les 5 statuts du CDC §9, avec leur libellé. */
    public const STATUTS = [
        'en_attente' => 'En attente',
        'confirme' => 'Confirmé',
        'refuse' => 'Refusé',
        'annule' => 'Annulé',
        'termine' => 'Terminé',
    ];

    /** Périodes souhaitées (décision n°4 : créneau souple). */
    public const PERIODES = [
        'matin' => 'Matin',
        'apres_midi' => 'Après-midi',
        'soir' => 'Soir',
    ];

    /** Durée par défaut (minutes) utilisée pour estimer les conflits. */
    public const DUREE_PAR_DEFAUT = 60;

    protected $fillable = [
        'statut',
        'source',
        'nom_contact',
        'moyen_contact',
        'date_souhaitee',
        'periode_souhaitee',
        'message',
        'prestation_id',
        'realisation_id',
        'debut',
        'duree_minutes',
        'notes_privees',
    ];

    protected function casts(): array
    {
        return [
            'date_souhaitee' => 'date',
            'debut' => 'datetime',
            'duree_minutes' => 'integer',
        ];
    }

    /** @return BelongsTo<Prestation, $this> */
    public function prestation(): BelongsTo
    {
        return $this->belongsTo(Prestation::class);
    }

    /** @return BelongsTo<Realisation, $this> */
    public function realisation(): BelongsTo
    {
        return $this->belongsTo(Realisation::class);
    }

    /** @param Builder<RendezVous> $query */
    public function scopeEnAttente(Builder $query): void
    {
        $query->where('statut', 'en_attente');
    }

    /** @param Builder<RendezVous> $query */
    public function scopeConfirmes(Builder $query): void
    {
        $query->where('statut', 'confirme');
    }

    public function statutLisible(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    /** Classe de badge Bootstrap associée au statut. */
    public function couleurBadge(): string
    {
        return match ($this->statut) {
            'en_attente' => 'text-bg-warning',
            'confirme' => 'text-bg-success',
            'termine' => 'text-bg-info',
            default => 'text-bg-secondary', // refusé, annulé
        };
    }

    public function periodeLisible(): ?string
    {
        return $this->periode_souhaitee ? (self::PERIODES[$this->periode_souhaitee] ?? null) : null;
    }

    /** Souhait exprimé par la cliente, lisible (ex. « mercredi 12/11/2026, après-midi »). */
    public function souhaitAffiche(): ?string
    {
        $parties = array_filter([
            $this->date_souhaitee?->translatedFormat('l d/m/Y'),
            $this->periodeLisible() ? mb_strtolower($this->periodeLisible()) : null,
        ]);

        return $parties === [] ? null : implode(', ', $parties);
    }

    /** Créneau planifié, lisible (ex. « mercredi 12/11/2026 à 14 h 30 »). */
    public function horaireAffiche(): ?string
    {
        if (! $this->debut) {
            return null;
        }

        $texte = $this->debut->translatedFormat('l d/m/Y').' à '.$this->debut->format('G\\ \\h\\ i');

        if ($this->duree_minutes) {
            $heures = intdiv($this->duree_minutes, 60);
            $minutes = $this->duree_minutes % 60;
            $duree = trim(($heures ? "{$heures} h" : '').($minutes ? " {$minutes} min" : ''));
            $texte .= " ({$duree})";
        }

        return $texte;
    }

    /** Fin estimée du créneau (durée par défaut : 1 h). */
    public function fin(): ?Carbon
    {
        return $this->debut?->copy()->addMinutes($this->duree_minutes ?? self::DUREE_PAR_DEFAUT);
    }

    /**
     * Conflits de créneaux (CDC §9) : autres rendez-vous CONFIRMÉS du même
     * jour dont le créneau chevauche celui-ci. Simple signalement — aucun
     * blocage, la propriétaire décide.
     *
     * @return Collection<int, RendezVous>
     */
    public function conflits(): Collection
    {
        if (! $this->debut || $this->statut !== 'confirme') {
            return new Collection;
        }

        return static::query()
            ->confirmes()
            ->whereNotNull('debut')
            ->whereKeyNot($this->getKey())
            ->whereDate('debut', $this->debut->toDateString())
            ->get()
            ->filter(fn (RendezVous $autre) => $autre->debut->lt($this->fin()) && $autre->fin()->gt($this->debut));
    }

    /**
     * Lien « Répondre sur WhatsApp » (décision n°16) : seulement si le
     * contact laissé ressemble à un numéro de téléphone. Jamais d'envoi
     * automatique — le brouillon s'ouvre dans WhatsApp, c'est tout.
     */
    public function lienWhatsappReponse(): ?string
    {
        // Uniquement des caractères téléphoniques (chiffres, +, espaces, ponctuation légère).
        if (! $this->moyen_contact || ! preg_match('/^[\d\s+().\/-]+$/', $this->moyen_contact)) {
            return null;
        }

        $numero = Telephone::international($this->moyen_contact);

        if ($numero === null) {
            return null;
        }

        $message = "Bonjour {$this->nom_contact}, merci pour votre demande de rendez-vous !";

        return 'https://wa.me/'.$numero.'?text='.rawurlencode($message);
    }
}
