<?php

namespace App\Console\Commands;

use App\Models\Release;
use Illuminate\Console\Command;

class PortalReleases extends Command
{
    protected $signature = 'portal:releases';

    protected $description = 'List the published game builds with sizes and download counts';

    public function handle(): int
    {
        $rows = Release::orderByDesc('released_at')->orderBy('platform')->get()
            ->map(fn (Release $r) => [$r->version, $r->platformLabel(), $r->arch, $r->humanSize(), $r->downloads, $r->released_at?->toDateString(), $r->file_name]);
        if ($rows->isEmpty()) {
            $this->warn('No releases published.');

            return self::SUCCESS;
        }
        $this->table(['Version', 'Platform', 'Arch', 'Size', 'Downloads', 'Released', 'File'], $rows);

        return self::SUCCESS;
    }
}
