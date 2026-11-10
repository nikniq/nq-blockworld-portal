<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PortalUsers extends Command
{
    protected $signature = 'portal:users
        {--admin= : handle to grant admin rights}
        {--revoke= : handle to remove admin rights from}
        {--delete= : handle to delete, with their shared worlds}
        {--limit=20 : how many recent players to list}';

    protected $description = 'List recent players; grant or revoke admin; delete an account';

    public function handle(): int
    {
        foreach (['admin' => true, 'revoke' => false] as $opt => $flag) {
            if ($handle = $this->option($opt)) {
                $user = User::where('handle', strtolower($handle))->first();
                if (! $user) {
                    $this->error("No player with handle $handle.");

                    return self::FAILURE;
                }
                $user->forceFill(['is_admin' => $flag])->save();
                $this->info("@{$user->handle} is ".($flag ? 'now' : 'no longer').' an admin.');
            }
        }
        if ($handle = $this->option('delete')) {
            $user = User::where('handle', strtolower($handle))->first();
            if (! $user) {
                $this->error("No player with handle $handle.");

                return self::FAILURE;
            }
            $n = $user->games()->count();
            if (! $this->confirm("Delete @{$user->handle} ({$user->email}) and their $n shared world(s)?")) {
                return self::SUCCESS;
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
            $this->info("Deleted @{$user->handle}.");
        }

        $rows = User::withCount('games')->latest()->take((int) $this->option('limit'))->get()
            ->map(fn (User $u) => [$u->handle, $u->name, $u->email, $u->google_id ? 'google' : 'password', $u->is_admin ? 'yes' : '', $u->games_count, $u->created_at->toDateString()]);
        $this->table(['Handle', 'Name', 'Email', 'Login', 'Admin', 'Worlds', 'Joined'], $rows);
        $this->line(User::count().' players, '.User::where('is_admin', true)->count().' admin(s), '.User::whereNotNull('google_id')->count().' via Google.');

        return self::SUCCESS;
    }
}
