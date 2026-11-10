<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

/**
 * "Continue with Google": signs in an account already linked to the Google
 * ID, links an existing account with the same (verified) email, or creates
 * a new player with a handle derived from the Google name.
 */
class GoogleController extends Controller
{
    public static function enabled(): bool
    {
        return (bool) config('services.google.client_id');
    }

    public function redirect(): RedirectResponse
    {
        abort_unless(self::enabled(), 404);

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        abort_unless(self::enabled(), 404);
        try {
            $google = Socialite::driver('google')->user();
        } catch (InvalidStateException|ClientException) {
            return redirect()->route('login')->withErrors(['email' => 'Google sign-in did not complete. Please try again.']);
        }

        $user = User::where('google_id', $google->getId())->first();
        $created = false;
        if (! $user && $google->getEmail()) {
            $user = User::where('email', $google->getEmail())->first();
            $user?->forceFill(['google_id' => $google->getId()])->save();
        }
        if (! $user) {
            if (! $google->getEmail()) {
                return redirect()->route('login')->withErrors(['email' => 'Google did not share an email address, so no account could be created.']);
            }
            $user = User::create([
                'name' => Str::limit($google->getName() ?: Str::before($google->getEmail(), '@'), 60, ''),
                'handle' => self::freeHandle($google->getNickname() ?: $google->getName() ?: Str::before($google->getEmail(), '@')),
                'email' => $google->getEmail(),
                'password' => null,
            ]);
            $user->forceFill(['google_id' => $google->getId(), 'email_verified_at' => now()])->save();
            $created = true;
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        if ($created) {
            return redirect()->route('profile.edit')->with('status', 'Welcome to Blockworld, '.$user->name.'! Your handle is @'.$user->handle.'; fill in your profile.');
        }

        return redirect()->intended(route('profile.show', $user));
    }

    /** A handle (letters, digits, underscore, 3-32 chars) not yet taken. */
    public static function freeHandle(string $from): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9_]+/i', '_', trim($from)));
        $base = trim(substr($base, 0, 24), '_') ?: 'player';
        if (strlen($base) < 3) {
            $base = str_pad($base, 3, '_');
        }
        $handle = $base;
        for ($n = 2; User::where('handle', $handle)->exists(); $n++) {
            $handle = $base.$n;
        }

        return $handle;
    }
}
