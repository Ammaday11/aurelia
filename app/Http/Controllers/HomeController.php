<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featured = Product::query()
            ->with('images')
            ->active()
            ->featured()
            ->latest()
            ->limit(8)
            ->get();

        // Pad out the "featured" rail with the newest active products if
        // fewer than 8 are explicitly marked featured, so the home page
        // never looks sparse on a fresh install.
        if ($featured->count() < 8) {
            $fillerIds = $featured->pluck('id');
            $filler = Product::query()
                ->with('images')
                ->active()
                ->whereKeyNot($fillerIds)
                ->latest()
                ->limit(8 - $featured->count())
                ->get();

            $featured = $featured->concat($filler);
        }

        return view('pages.home', [
            'featured' => $featured,
            'categories' => Category::active()->ordered()->withCount('activeProducts')->get(),
        ]);
    }
}
