<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategorieController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ParametreController;
use App\Http\Controllers\Admin\PrestationController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\RealisationController;
use App\Http\Controllers\Public\AccueilController;
use App\Http\Controllers\Public\GalerieController;
use App\Http\Controllers\Public\PrestationsController;
use App\Http\Controllers\Public\TemoignagesController;
use App\Http\Controllers\Public\RendezVousController as PublicRendezVousController;
use App\Http\Controllers\Admin\RendezVousController;
use App\Http\Controllers\Admin\TemoignageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes publiques (famille « public.* »)
|--------------------------------------------------------------------------
| Toutes les routes sont nommées (exigence du cahier des charges §11).
| Les vues et redirections utilisent exclusivement ces noms.
*/

Route::name('public.')->group(function () {
    Route::get('/', AccueilController::class)->name('home');
    Route::get('realisations', [GalerieController::class, 'index'])->name('galerie');
    Route::get('realisations/{reference}', [GalerieController::class, 'show'])->name('realisation');
    Route::get('prestations', PrestationsController::class)->name('prestations');
    Route::get('temoignages', TemoignagesController::class)->name('temoignages');

    Route::get('rendez-vous', [PublicRendezVousController::class, 'create'])->name('rdv.create');
    Route::post('rendez-vous', [PublicRendezVousController::class, 'store'])->name('rdv.store');
});

/*
|--------------------------------------------------------------------------
| Routes d'administration (famille « admin.* »)
|--------------------------------------------------------------------------
| Protégées par authentification ; aucune inscription publique (CDC §12).
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('connexion', [AuthController::class, 'create'])->name('login');
        Route::post('connexion', [AuthController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('parametres', [ParametreController::class, 'edit'])->name('parametres.edit');
        Route::put('parametres', [ParametreController::class, 'update'])->name('parametres.update');

        Route::resource('categories', CategorieController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['categories' => 'categorie']);
        Route::post('categories/{categorie}/deplacer', [CategorieController::class, 'deplacer'])
            ->name('categories.deplacer');

        Route::resource('realisations', RealisationController::class)
            ->except(['show']);
        Route::post('realisations/{realisation}/publication', [RealisationController::class, 'basculerPublication'])
            ->name('realisations.publication');
        Route::post('realisations/{realisation}/deplacer', [RealisationController::class, 'deplacer'])
            ->name('realisations.deplacer');

        Route::resource('prestations', PrestationController::class)->except(['show']);
        Route::post('prestations/{prestation}/publication', [PrestationController::class, 'basculerPublication'])
            ->name('prestations.publication');
        Route::post('prestations/{prestation}/deplacer', [PrestationController::class, 'deplacer'])
            ->name('prestations.deplacer');

        Route::resource('promotions', PromotionController::class)->except(['show']);
        Route::post('promotions/{promotion}/publication', [PromotionController::class, 'basculerPublication'])
            ->name('promotions.publication');

        Route::resource('temoignages', TemoignageController::class)->except(['show']);
        Route::post('temoignages/{temoignage}/publication', [TemoignageController::class, 'basculerPublication'])
            ->name('temoignages.publication');

        // Rendez-vous : routes manuelles (« rendez-vous » se singularise mal).
        Route::get('rendez-vous', [RendezVousController::class, 'index'])->name('rdv.index');
        Route::get('rendez-vous/calendrier', [RendezVousController::class, 'calendrier'])->name('rdv.calendrier');
        Route::get('rendez-vous/nouveau', [RendezVousController::class, 'create'])->name('rdv.create');
        Route::post('rendez-vous', [RendezVousController::class, 'store'])->name('rdv.store');
        Route::get('rendez-vous/{rendezVous}/modifier', [RendezVousController::class, 'edit'])->name('rdv.edit');
        Route::put('rendez-vous/{rendezVous}', [RendezVousController::class, 'update'])->name('rdv.update');
        Route::post('rendez-vous/{rendezVous}/statut', [RendezVousController::class, 'changerStatut'])->name('rdv.statut');
        Route::delete('rendez-vous/{rendezVous}', [RendezVousController::class, 'destroy'])->name('rdv.destroy');

        Route::post('deconnexion', [AuthController::class, 'destroy'])->name('logout');
    });
});
