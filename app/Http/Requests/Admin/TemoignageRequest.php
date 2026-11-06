<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TemoignageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'auteur' => ['required', 'string', 'max:100'],
            'contenu' => ['required', 'string', 'max:2000'],
            'autorisation_affichage' => ['nullable', 'boolean'],
            'autorisation_notee_le' => ['nullable', 'date', 'before_or_equal:today'],
            'autorisation_note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'auteur' => 'prénom ou pseudonyme',
            'contenu' => 'témoignage',
            'autorisation_notee_le' => 'date de l\'autorisation',
            'autorisation_note' => 'note sur l\'autorisation',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'autorisation_notee_le.before_or_equal' => 'La date de l\'autorisation ne peut pas être dans le futur.',
        ];
    }
}
