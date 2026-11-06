<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Tableau de bord de l'administration (CDC §9).
 *
 * V1 de l'étape 1 : page d'accueil de l'admin ; les indicateurs
 * (demandes récentes, rendez-vous à venir…) seront branchés au fil
 * des étapes 2 à 8, quand leurs tables existeront.
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard');
    }
}
