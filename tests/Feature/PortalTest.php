<?php

namespace Tests\Feature;

use App\Models\Release;
use App\Models\SharedGame;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_introduces_the_game(): void
    {
        $this->get('/')->assertOk()->assertSee('Mine by day')->assertSee('Download');
    }

    public function test_download_page_lists_releases_and_serves_files(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('releases/Blockworld-1.0-macos-arm64.zip', 'zip-bytes');
        $release = Release::factory()->create();

        $this->get('/download')->assertOk()->assertSee('macOS')->assertSee('v1.0');
        $this->get(route('downloads.file', $release))->assertOk()->assertDownload('Blockworld-1.0-macos-arm64.zip');
        $this->assertSame(1, $release->fresh()->downloads);
    }

    public function test_user_can_register_and_edit_profile(): void
    {
        $this->post('/register', [
            'name' => 'Terence', 'handle' => 'Terry_1', 'email' => 't@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123', 'platform' => 'macos',
        ])->assertRedirect(route('profile.edit'));
        $user = User::where('email', 't@example.com')->firstOrFail();
        $this->assertSame('terry_1', $user->handle);

        $this->actingAs($user)->put('/profile', [
            'name' => 'Terence', 'bio' => 'Dragon hunter', 'favourite_mode' => 'battle', 'best_score' => 12345,
        ])->assertRedirect(route('profile.show', $user));
        $this->get('/u/terry_1')->assertOk()->assertSee('Dragon hunter')->assertSee('12,345')->assertSee('Battle');
        $this->get('/players')->assertOk()->assertSee('Terence');
    }

    public function test_user_can_share_download_and_delete_a_world(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/share', [
            'title' => 'Fortress on the Peak', 'description' => 'Base next to the fire dragon.',
            'mode' => 'survival', 'world_size' => 'huge', 'score' => 5000, 'nights' => 12,
            'save' => UploadedFile::fake()->createWithContent('world.sav', str_repeat('x', 1024)),
            'screenshot' => UploadedFile::fake()->image('shot.png'),
        ])->assertRedirect('/games/fortress-on-the-peak');

        $game = SharedGame::firstOrFail();
        Storage::disk('local')->assertExists($game->save_path);
        Storage::disk('public')->assertExists($game->screenshot_path);

        $this->get('/games')->assertOk()->assertSee('Fortress on the Peak');
        $this->get('/games/fortress-on-the-peak')->assertOk()->assertSee('fire dragon');
        $this->get('/games/fortress-on-the-peak/download')->assertOk()->assertDownload('world.sav');

        $other = User::factory()->create();
        $this->actingAs($other)->delete('/games/fortress-on-the-peak')->assertForbidden();
        $this->actingAs($user)->delete('/games/fortress-on-the-peak')->assertRedirect('/games');
        Storage::disk('local')->assertMissing($game->save_path);
    }

    public function test_sharing_requires_login_and_a_sav_file(): void
    {
        $this->get('/share')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->post('/share', [
            'title' => 'Bad', 'mode' => 'survival', 'world_size' => 'large',
            'save' => UploadedFile::fake()->create('evil.exe', 10),
        ])->assertSessionHasErrors('save');
    }
}
