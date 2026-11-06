<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation de l'écran Paramètres : les règles sont dérivées du registre
 * config/parametres.php, unique source de vérité des champs éditables.
 */
class ParametresRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $regles = [];

        foreach (config('parametres') as $champs) {
            foreach ($champs as $cle => $champ) {
                $regles[$cle] = match ($champ['type']) {
                    'textarea' => ['nullable', 'string', 'max:2000'],
                    'email' => ['nullable', 'string', 'email', 'max:255'],
                    'url' => ['nullable', 'string', 'url', 'max:255'],
                    default => ['nullable', 'string', 'max:255'],
                };
            }
        }

        return $regles;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributs = [];

        foreach (config('parametres') as $champs) {
            foreach ($champs as $cle => $champ) {
                $attributs[$cle] = mb_strtolower($champ['libelle']);
            }
        }

        return $attributs;
    }
}
