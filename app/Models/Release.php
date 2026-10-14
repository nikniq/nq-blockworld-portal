<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['version', 'platform', 'arch', 'file_name', 'file_path', 'size_bytes', 'sha256', 'notes', 'released_at'])]
class Release extends Model
{
    use HasFactory;

    public const PLATFORMS = User::PLATFORMS;

    protected function casts(): array
    {
        return ['released_at' => 'datetime'];
    }

    public function platformLabel(): string
    {
        return self::PLATFORMS[$this->platform] ?? ucfirst($this->platform);
    }

    public function humanSize(): string
    {
        return humanSize($this->size_bytes);
    }
}
