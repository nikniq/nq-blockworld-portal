<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['title', 'description', 'mode', 'world_size', 'score', 'nights', 'save_path', 'save_size', 'screenshot_path'])]
class SharedGame extends Model
{
    use HasFactory;

    public const MODES = User::MODES;

    public const SIZES = ['normal' => 'Normal (192x192)', 'large' => 'Large (384x384)', 'huge' => 'Huge (576x576)'];

    protected static function booted(): void
    {
        static::creating(function (SharedGame $game) {
            $game->slug ??= self::uniqueSlug($game->title);
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::limit(Str::slug($title) ?: 'world', 48, '');
        $slug = $base;
        for ($i = 2; static::where('slug', $slug)->exists(); $i++) {
            $slug = "$base-$i";
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function modeLabel(): string
    {
        return self::MODES[$this->mode] ?? ucfirst($this->mode);
    }

    public function sizeLabel(): string
    {
        return self::SIZES[$this->world_size] ?? ucfirst($this->world_size);
    }

    public function humanSize(): string
    {
        return humanSize($this->save_size);
    }
}
