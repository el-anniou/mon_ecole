<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Prestation;
use App\Models\Promotion;
use Illuminate\View\View;

/**
 * Page publique des prestations et promotions actives (CDC §5 et §7).
 */
class PrestationsController extends Controller
{
    public function __invoke(): View
    {
        return view('public.prestations', [
            'prestations' => Prestation::query()->publiees()->ordonnees()->get(),
            'promotions' => Promotion::query()->actives()->orderBy('fin')->get(),
        ]);
    }
}
