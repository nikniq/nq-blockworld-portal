<?php

namespace Database\Seeders;

use App\Models\Release;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Imports packaged builds from the game repo's dist/ folder (see nq-blockworld/cx.sh).
 * Set BLOCKWORLD_DIST to point elsewhere. Files are copied under storage/app/private/releases.
 */
class ReleaseSeeder extends Seeder
{
    public function run(): void
    {
        $dist = env('BLOCKWORLD_DIST', base_path('../nq-blockworld/dist'));
        if (! is_dir($dist)) {
            $this->command?->warn("No dist folder at $dist; skipping release import.");

            return;
        }

        $sums = [];
        if (is_file("$dist/SHA256SUMS")) {
            foreach (file("$dist/SHA256SUMS", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                [$hash, $name] = preg_split('/\s+/', trim($line), 2);
                $sums[$name] = $hash;
            }
        }

        foreach (glob("$dist/*.{zip,tar.gz}", GLOB_BRACE) as $file) {
            $name = basename($file);
            // Blockworld-1.0-macos-arm64.zip / blockworld-1.0-linux-amd64.tar.gz / blockworld-1.0-windows-amd64.zip
            if (! preg_match('/^blockworld-([\d.]+)-(macos|linux|windows)-(\w+)\.(zip|tar\.gz)$/i', $name, $m)) {
                continue;
            }
            $path = "releases/$name";
            Storage::disk('local')->put($path, fopen($file, 'r'));
            Release::updateOrCreate(
                ['version' => $m[1], 'platform' => strtolower($m[2]), 'arch' => $m[3]],
                [
                    'file_name' => $name,
                    'file_path' => $path,
                    'size_bytes' => filesize($file),
                    'sha256' => $sums[$name] ?? hash_file('sha256', $file),
                    'released_at' => now()->setTimestamp(filemtime($file)),
                ],
            );
            $this->command?->info("Imported $name");
        }
    }
}
