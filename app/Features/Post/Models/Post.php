<?php

namespace App\Features\Post\Models;

use App\Models\User;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'title', 'slug', 'category', 'excerpt', 'content', 'cover_image_path', 'is_published', 'published_at'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'posts';

    /**
     * Accessor yang selalu ikut diserialisasi ke frontend.
     *
     * @var array<int, string>
     */
    protected $appends = ['cover_url'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * URL publik (relatif) gambar sampul artikel.
     *
     * @return Attribute<string|null, never>
     */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->cover_image_path ? '/storage/'.ltrim($this->cover_image_path, '/') : null);
    }

    /**
     * Penulis artikel.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }
}
