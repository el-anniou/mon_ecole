<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation d'une réalisation (CDC §6) :
 * image obligatoire à la création (contrôle type/poids), titre facultatif.
 */
class RealisationRequest extends FormRequest
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
        $creation = $this->route('realisation') === null;

        return [
            'image' => [
                $creation ? 'required' : 'nullable',
                'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192',
            ],
            'titre' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'texte_alternatif' => ['nullable', 'string', 'max:255'],
            'realisee_le' => ['nullable', 'date'],
            'publiee' => ['nullable', 'boolean'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'image' => 'image principale',
            'titre' => 'titre',
            'description' => 'description',
            'texte_alternatif' => 'texte alternatif',
            'realisee_le' => 'date de réalisation',
            'categories' => 'catégories',
        ];
    }
}
