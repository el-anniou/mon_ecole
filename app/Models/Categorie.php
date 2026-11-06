<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Categorie extends Model
{
    /** @use HasFactory<\Database\Factories\CategorieFactory> */
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = ['nom', 'slug', 'position', 'visible'];

    protected function casts(): array
    {
        return [
            'visible' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $categorie) {
            $categorie->slug = $categorie->slug ?: static::slugUnique($categorie->nom);

            if ($categorie->position === 0 || $categorie->position === null) {
                $categorie->position = (int) static::query()->max('position') + 1;
            }
        });
    }

    /** @return BelongsToMany<Realisation, $this> */
    public function realisations(): BelongsToMany
    {
        return $this->belongsToMany(Realisation::class, 'categorie_realisation');
    }

    /** @param Builder<Categorie> $query */
    public function scopeVisibles(Builder $query): void
    {
        $query->where('visible', true);
    }

    /** @param Builder<Categorie> $query */
    public function scopeOrdonnees(Builder $query): void
    {
        $query->orderBy('position')->orderBy('nom');
    }

    /**
     * Génère un slug unique à partir du nom (ex. « Mariée » → mariee, mariee-2…).
     */
    public static function slugUnique(string $nom, ?int $ignorerId = null): string
    {
        $base = Str::slug($nom) ?: 'categorie';
        $slug = $base;
        $suffixe = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignorerId, fn ($q) => $q->whereKeyNot($ignorerId))
            ->exists()) {
            $slug = $base.'-'.$suffixe++;
        }

        return $slug;
    }
}
