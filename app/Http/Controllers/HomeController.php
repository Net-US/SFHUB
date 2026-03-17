<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LandingContent;

class HomeController extends Controller
{
    /**
     * Display the landing page.
     */
    public function index()
    {
        $features = LandingContent::active()->bySection('features')->orderBy('sort_order')->get();
        $stats    = LandingContent::active()->bySection('stats')->orderBy('sort_order')->get();
        $hero     = LandingContent::where('key', 'hero_title')->first();

        return view('home', compact('features', 'stats', 'hero'));
    }
}
