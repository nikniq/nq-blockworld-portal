<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class PortalPrune extends Command
{
    protected $signature = 'portal:prune {--days=30 : delete database backups older than this}';

    protected $description = 'Remove expired sessions, expired cache rows and old database backups';

    public function handle(): int
    {
        if (config('session.driver') === 'database' && Schema::hasTable('sessions')) {
            $cutoff = now()->subMinutes((int) config('session.lifetime'))->getTimestamp();
            $this->line(DB::table('sessions')->where('last_activity', '<', $cutoff)->delete().' expired sessions removed');
        }
        if (config('cache.default') === 'database' && Schema::hasTable('cache')) {
            $this->line(DB::table('cache')->where('expiration', '<', now()->getTimestamp())->delete().' expired cache rows removed');
        }
        $dir = storage_path('backups');
        $removed = 0;
        if (File::isDirectory($dir)) {
            $limit = now()->subDays((int) $this->option('days'))->getTimestamp();
            foreach (File::glob($dir.'/db-*.gz') as $f) {
                if (File::lastModified($f) < $limit) {
                    File::delete($f);
                    $removed++;
                }
            }
        }
        $this->line("$removed backup(s) older than {$this->option('days')} days removed");

        return self::SUCCESS;
    }
}
