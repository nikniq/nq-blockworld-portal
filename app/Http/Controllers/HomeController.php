<?php

namespace App\Http\Controllers;

use App\Models\Release;
use App\Models\SharedGame;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'latest' => Release::latest('released_at')->first(),
            'games' => SharedGame::with('user')->latest()->take(6)->get(),
        ]);
    }
}
