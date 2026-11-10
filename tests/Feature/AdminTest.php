<?php

namespace Tests\Feature;

use App\Models\Release;
use App\Models\SharedGame;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_area_is_for_admins_only(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
        $this->actingAs(User::factory()->create())->get('/')->assertDontSee('Admin</a>', false);

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/')->assertSee('Admin');
        foreach (['/admin', '/admin/players', '/admin/worlds', '/admin/releases'] as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
    }

    public function test_admin_manages_players(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $other = User::factory()->create(['handle' => 'grace']);
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('local')->put('games/x.sav', 'save');
        $game = SharedGame::factory()->for($other)->create(['save_path' => 'games/x.sav']);

        $this->actingAs($admin)->get('/admin/players?q=grace')->assertOk()->assertSee('grace');
        $this->actingAs($admin)->post(route('admin.users.admin', $other))->assertRedirect();
        $this->assertTrue($other->fresh()->is_admin);
        $this->actingAs($admin)->post(route('admin.users.admin', $admin))->assertSessionHasErrors('admin');
        $this->assertTrue($admin->fresh()->is_admin);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $other))->assertRedirect(route('admin.users'));
        $this->assertNull($other->fresh());
        $this->assertNull($game->fresh());
        Storage::disk('local')->assertMissing('games/x.sav');
    }

    public function test_admin_removes_worlds_and_releases(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Storage::fake('local');
        Storage::disk('local')->put('games/y.sav', 'save');
        Storage::disk('local')->put('releases/Blockworld-1.0-macos-arm64.zip', 'zip');
        $game = SharedGame::factory()->create(['save_path' => 'games/y.sav', 'title' => 'Cliff fort']);
        $release = Release::factory()->create();

        $this->actingAs($admin)->get('/admin/worlds')->assertSee('Cliff fort');
        $this->actingAs($admin)->delete(route('admin.games.destroy', $game))->assertRedirect();
        $this->assertNull($game->fresh());
        Storage::disk('local')->assertMissing('games/y.sav');

        $this->actingAs($admin)->get('/admin/releases')->assertSee('on disk');
        $this->actingAs($admin)->put(route('admin.releases.update', $release), ['notes' => 'Unzip and run.'])->assertRedirect();
        $this->assertSame('Unzip and run.', $release->fresh()->notes);
        $this->actingAs($admin)->delete(route('admin.releases.destroy', $release))->assertRedirect();
        $this->assertNull($release->fresh());
        Storage::disk('local')->assertMissing('releases/Blockworld-1.0-macos-arm64.zip');
    }
}
