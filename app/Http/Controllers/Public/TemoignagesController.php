<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Temoignage;
use Illuminate\View\View;

/**
 * Page publique des témoignages (CDC §5 et §8) : uniquement les
 * témoignages publiés ET autorisés, les plus récents en premier.
 */
class TemoignagesController extends Controller
{
    public function __invoke(): View
    {
        return view('public.temoignages', [
            'temoignages' => Temoignage::query()->publies()->latest()->orderByDesc('id')->get(),
        ]);
    }
}
