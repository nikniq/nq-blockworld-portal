<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        return view('profile.index', [
            'players' => User::withCount('games')->orderByDesc('best_score')->orderBy('name')->paginate(24),
        ]);
    }

    public function show(User $user): View
    {
        return view('profile.show', ['user' => $user, 'games' => $user->games()->get()]);
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'bio' => ['nullable', 'string', 'max:500'],
            'platform' => ['nullable', 'in:'.implode(',', array_keys(User::PLATFORMS))],
            'favourite_mode' => ['nullable', 'in:'.implode(',', array_keys(User::MODES))],
            'best_score' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }
        $data['best_score'] ??= 0;
        unset($data['avatar']);
        $user->update($data);

        return redirect()->route('profile.show', $user)->with('status', 'Profile saved.');
    }
}
