<?php

namespace App\Http\Controllers;

use App\Models\Flower;
use Illuminate\View\View;

class CustomBouquetController extends Controller
{
    public function index(): View
    {
        return view('pages.custom-bouquet', [
            'flowers' => Flower::query()->active()->ordered()->get(),
        ]);
    }
}
