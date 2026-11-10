<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $users = User::withCount('games')->latest()
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('handle', 'like', "%$q%")->orWhere('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%")))
            ->paginate(30)->withQueryString();

        return view('admin.users', compact('users', 'q'));
    }

    public function toggleAdmin(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['admin' => 'You cannot change your own admin rights.']);
        }
        $user->forceFill(['is_admin' => ! $user->is_admin])->save();

        return back()->with('status', '@'.$user->handle.' is '.($user->is_admin ? 'now' : 'no longer').' an admin.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['admin' => 'You cannot delete your own account from here.']);
        }
        foreach ($user->games as $game) {
            Storage::disk('local')->delete($game->save_path);
            if ($game->screenshot_path) {
                Storage::disk('public')->delete($game->screenshot_path);
            }
            $game->delete();
        }
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }
        $user->delete();

        return redirect()->route('admin.users')->with('status', 'Deleted @'.$user->handle.' and their worlds.');
    }
}
