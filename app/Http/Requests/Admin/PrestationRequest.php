<?php

namespace App\Http\Requests\Admin;

use App\Models\Prestation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation d'une prestation (CDC §7) : le montant exigé dépend
 * du type de prix choisi — jamais de prix fictif imposé.
 */
class PrestationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type_prix' => ['required', Rule::in(array_keys(Prestation::TYPES_PRIX))],
            'prix_min' => [
                'nullable', 'numeric', 'min:0', 'max:99999999',
                Rule::requiredIf(fn () => in_array($this->input('type_prix'), ['fixe', 'a_partir_de', 'fourchette'])),
            ],
            'prix_max' => [
                'nullable', 'numeric', 'min:0', 'max:99999999', 'gte:prix_min',
                Rule::requiredIf(fn () => $this->input('type_prix') === 'fourchette'),
            ],
            'duree_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
            'publiee' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nom' => 'nom de la prestation',
            'type_prix' => 'type de prix',
            'prix_min' => 'prix (ou borne basse)',
            'prix_max' => 'borne haute',
            'duree_minutes' => 'durée indicative',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'prix_min.required' => 'Ce type de prix nécessite un montant.',
            'prix_max.required' => 'Une fourchette nécessite une borne haute.',
            'prix_max.gte' => 'La borne haute doit être supérieure ou égale à la borne basse.',
        ];
    }
}
