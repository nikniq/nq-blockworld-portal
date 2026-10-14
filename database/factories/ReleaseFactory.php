<?php

namespace Database\Factories;

use App\Models\Release;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Release> */
class ReleaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'version' => '1.0',
            'platform' => 'macos',
            'arch' => 'arm64',
            'file_name' => 'Blockworld-1.0-macos-arm64.zip',
            'file_path' => 'releases/Blockworld-1.0-macos-arm64.zip',
            'size_bytes' => 2585251,
            'sha256' => str_repeat('0', 64),
            'released_at' => now(),
        ];
    }
}
