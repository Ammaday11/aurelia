<?php

namespace App\Http\Controllers;

use App\Models\Flower;
use App\Models\Ribbon;
use App\Models\WrappingPaper;
use Illuminate\View\View;

class CustomBouquetController extends Controller
{
    public function index(): View
    {
        return view('pages.custom-bouquet', [
            'flowers' => Flower::query()->active()->ordered()->get(),
            'wrappingPapers' => WrappingPaper::query()->active()->ordered()->get(),
            'ribbons' => Ribbon::query()->active()->ordered()->get(),
        ]);
    }
}
