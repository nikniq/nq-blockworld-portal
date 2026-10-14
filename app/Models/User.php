<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'handle', 'email', 'password', 'bio', 'platform', 'favourite_mode', 'best_score', 'avatar_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const PLATFORMS = ['macos' => 'macOS', 'linux' => 'Linux', 'windows' => 'Windows'];

    public const MODES = ['survival' => 'Survival', 'creative' => 'Creative', 'zombie' => 'Zombie', 'battle' => 'Battle'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function games(): HasMany
    {
        return $this->hasMany(SharedGame::class)->latest();
    }

    public function getRouteKeyName(): string
    {
        return 'handle';
    }
}
