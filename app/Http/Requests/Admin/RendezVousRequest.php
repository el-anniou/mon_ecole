<?php

namespace App\Http\Requests\Admin;

use App\Models\RendezVous;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RendezVousRequest extends FormRequest
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
            'statut' => ['required', Rule::in(array_keys(RendezVous::STATUTS))],
            'date_souhaitee' => ['nullable', 'date'],
            'periode_souhaitee' => ['nullable', Rule::in(array_keys(RendezVous::PERIODES))],
            'message' => ['nullable', 'string', 'max:1500'],
            'prestation_id' => ['nullable', Rule::exists('prestations', 'id')],
            'realisation_id' => ['nullable', Rule::exists('realisations', 'id')],
            'debut' => ['nullable', 'date'],
            'duree_minutes' => ['nullable', 'integer', 'min:5', 'max:600'],
            'notes_privees' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nom_contact' => 'nom',
            'moyen_contact' => 'moyen de contact',
            'date_souhaitee' => 'date souhaitée',
            'periode_souhaitee' => 'période souhaitée',
            'prestation_id' => 'prestation',
            'realisation_id' => 'modèle',
            'debut' => 'date et heure du rendez-vous',
            'duree_minutes' => 'durée',
            'notes_privees' => 'notes privées',
        ];
    }
}
