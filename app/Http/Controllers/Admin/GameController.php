<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SharedGame;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $games = SharedGame::with('user')->latest()
            ->when($q !== '', fn ($query) => $query->where('title', 'like', "%$q%")
                ->orWhereHas('user', fn ($u) => $u->where('handle', 'like', "%$q%")))
            ->paginate(30)->withQueryString();

        return view('admin.games', compact('games', 'q'));
    }

    public function destroy(SharedGame $game): RedirectResponse
    {
        Storage::disk('local')->delete($game->save_path);
        if ($game->screenshot_path) {
            Storage::disk('public')->delete($game->screenshot_path);
        }
        $game->delete();

        return back()->with('status', 'Removed "'.$game->title.'".');
    }
}
