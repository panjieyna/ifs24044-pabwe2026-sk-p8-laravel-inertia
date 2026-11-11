<?php

namespace App\Models;

use App\Features\Post\Models\Post;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'role', 'profile_photo_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_MEMBER = 'member';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Determine if the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Determine if the user is a standard member (penulis).
     */
    public function isMember(): bool
    {
        return $this->role === self::ROLE_MEMBER;
    }

    /**
     * URL publik (relatif) foto profil pengguna, null jika belum diunggah.
     *
     * @return Attribute<string|null, never>
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->profile_photo_path ? '/storage/'.ltrim($this->profile_photo_path, '/') : null);
    }

    /**
     * Semua artikel milik pengguna.
     *
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
