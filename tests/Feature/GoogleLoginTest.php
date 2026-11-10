<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\GoogleController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    private function googleReturns(string $id, string $email, string $name): void
    {
        $g = (new GoogleUser)->map(['id' => $id, 'email' => $email, 'name' => $name, 'nickname' => null]);
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($g);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_button_hidden_until_configured(): void
    {
        config(['services.google.client_id' => null]);
        $this->get('/login')->assertOk()->assertDontSee('Continue with Google');
        $this->get('/auth/google')->assertNotFound();

        config(['services.google.client_id' => 'abc']);
        $this->get('/login')->assertOk()->assertSee('Continue with Google');
        $this->get('/register')->assertOk()->assertSee('Continue with Google');
    }

    public function test_new_google_user_gets_an_account_and_a_free_handle(): void
    {
        config(['services.google.client_id' => 'abc']);
        User::factory()->create(['handle' => 'ada_lovelace']);
        $this->googleReturns('g-1', 'ada@example.com', 'Ada Lovelace');

        $this->get('/auth/google/callback')->assertRedirect(route('profile.edit'));
        $user = User::where('email', 'ada@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('ada_lovelace2', $user->handle);
        $this->assertSame('g-1', $user->google_id);
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_existing_email_is_linked_and_logged_in(): void
    {
        config(['services.google.client_id' => 'abc']);
        $user = User::factory()->create(['email' => 'bob@example.com']);
        $this->googleReturns('g-2', 'bob@example.com', 'Bob');

        $this->get('/auth/google/callback')->assertRedirect(route('profile.show', $user));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('g-2', $user->fresh()->google_id);
        $this->assertSame(1, User::count());
    }

    public function test_password_login_fails_cleanly_for_google_only_account(): void
    {
        $user = User::factory()->create(['password' => null, 'google_id' => 'g-3']);
        $this->post('/login', ['email' => $user->email, 'password' => 'anything'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_free_handle_cleans_names(): void
    {
        $this->assertSame('jean_luc', GoogleController::freeHandle('Jean-Luc!'));
        $this->assertSame('player', GoogleController::freeHandle('***'));
        $this->assertSame('al_', GoogleController::freeHandle('al'));
    }
}
