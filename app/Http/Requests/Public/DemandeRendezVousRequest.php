<?php

namespace App\Http\Requests\Public;

use App\Models\RendezVous;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Demande publique de rendez-vous, sans compte (CDC §8).
 * Créneau souple : date souhaitée + période + message libre.
 */
class DemandeRendezVousRequest extends FormRequest
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
            'nom_contact' => ['required', 'string', 'max:100'],
            'moyen_contact' => ['required', 'string', 'max:150'],
            'date_souhaitee' => ['nullable', 'date', 'after_or_equal:today'],
            'periode_souhaitee' => ['nullable', Rule::in(array_keys(RendezVous::PERIODES))],
            'message' => ['nullable', 'string', 'max:1500'],
            'prestation_id' => ['nullable', Rule::exists('prestations', 'id')->where('publiee', true)],
            'realisation_id' => ['nullable', Rule::exists('realisations', 'id')->where('publiee', true)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nom_contact' => 'nom ou prénom',
            'moyen_contact' => 'moyen de contact',
            'date_souhaitee' => 'date souhaitée',
            'periode_souhaitee' => 'période souhaitée',
            'prestation_id' => 'prestation',
            'realisation_id' => 'modèle',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_souhaitee.after_or_equal' => 'La date souhaitée ne peut pas être déjà passée.',
        ];
    }
}
