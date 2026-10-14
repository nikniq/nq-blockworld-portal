<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'handle' => ['required', 'string', 'min:3', 'max:32', 'regex:/^[a-z0-9_]+$/i', 'unique:users,handle'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'platform' => ['nullable', 'in:'.implode(',', array_keys(User::PLATFORMS))],
        ]);
        $data['handle'] = strtolower($data['handle']);

        $user = User::create($data);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('profile.edit')->with('status', 'Welcome to Blockworld, '.$user->name.'! Fill in your profile.');
    }
}
