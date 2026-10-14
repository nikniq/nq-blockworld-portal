<?php

namespace App\Console\Commands;

use App\Models\Release;
use App\Models\SharedGame;
use App\Models\User;
use Illuminate\Console\Command;

class PortalStats extends Command
{
    protected $signature = 'portal:stats';

    protected $description = 'Players, shared worlds and download totals';

    public function handle(): int
    {
        $this->table(['Metric', 'Value'], [
            ['Players', User::count()],
            ['Players joined last 7 days', User::where('created_at', '>=', now()->subDays(7))->count()],
            ['Shared worlds', SharedGame::count()],
            ['Worlds shared last 7 days', SharedGame::where('created_at', '>=', now()->subDays(7))->count()],
            ['World downloads', SharedGame::sum('downloads')],
            ['Game downloads', Release::sum('downloads')],
            ['Upload storage', humanSize((int) SharedGame::sum('save_size'))],
        ]);

        $top = SharedGame::with('user')->orderByDesc('downloads')->take(5)->get();
        if ($top->isNotEmpty()) {
            $this->newLine();
            $this->info('Most downloaded worlds');
            $this->table(['Title', 'By', 'Mode', 'Downloads'], $top->map(fn ($g) => [$g->title, $g->user->name, $g->modeLabel(), $g->downloads]));
        }

        return self::SUCCESS;
    }
}
