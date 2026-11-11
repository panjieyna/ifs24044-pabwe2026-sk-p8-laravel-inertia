<?php

namespace App\Features\Post\Repositories;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PostRepository implements PostRepositoryInterface
{
    /**
     * Kolom ringkas untuk daftar (konten lengkap tidak ikut dikirim agar halaman ringan).
     *
     * @var array<int, string>
     */
    public const LIST_COLUMNS = ['id', 'user_id', 'title', 'slug', 'category', 'excerpt', 'cover_image_path', 'is_published', 'published_at', 'created_at', 'updated_at'];

    /**
     * Relasi penulis yang dimuat untuk daftar artikel.
     */
    protected const AUTHOR_RELATION = 'user:id,name,username,profile_photo_path';

    public function forUser(int $userId, ?string $search = null, ?string $status = null, ?string $category = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Post::query()->select(self::LIST_COLUMNS)->where('user_id', $userId);

        return $this->applyFilters($query, $search, $status, $category)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function publishedPaginated(?string $search = null, ?string $category = null, int $perPage = 9): LengthAwarePaginator
    {
        $query = Post::query()->select(self::LIST_COLUMNS)->with(self::AUTHOR_RELATION)->where('is_published', true);

        return $this->applyFilters($query, $search, null, $category)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findPublishedBySlug(string $slug): ?Post
    {
        return Post::query()
            ->with(self::AUTHOR_RELATION)
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();
    }

    public function relatedPublished(Post $post, int $limit = 3): Collection
    {
        return Post::query()
            ->select(self::LIST_COLUMNS)
            ->with(self::AUTHOR_RELATION)
            ->where('is_published', true)
            ->where('category', $post->category)
            ->whereKeyNot($post->id)
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function allPaginated(?string $search = null, ?string $status = null, ?string $category = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Post::query()->select(self::LIST_COLUMNS)->with(self::AUTHOR_RELATION);

        return $this->applyFilters($query, $search, $status, $category)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function allForUser(int $userId): Collection
    {
        return Post::query()->with(self::AUTHOR_RELATION)->where('user_id', $userId)->latest('id')->get();
    }

    public function allWithAuthor(): Collection
    {
        return Post::query()->with(self::AUTHOR_RELATION)->latest('id')->get();
    }

    public function create(array $attributes): Post
    {
        return Post::create($attributes);
    }

    public function update(Post $post, array $attributes): bool
    {
        return $post->update($attributes);
    }

    public function delete(Post $post): bool
    {
        return (bool) $post->delete();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Post::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    public function statsForUser(int $userId): array
    {
        $total = Post::query()->where('user_id', $userId)->count();
        $published = Post::query()->where('user_id', $userId)->where('is_published', true)->count();

        return [
            'total' => $total,
            'published' => $published,
            'draft' => $total - $published,
        ];
    }

    /**
     * Terapkan filter pencarian (judul/ringkasan/konten), status, dan kategori.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    protected function applyFilters(Builder $query, ?string $search, ?string $status, ?string $category): Builder
    {
        $search = trim((string) $search);

        return $query
            ->when($search !== '', fn (Builder $q) => $q->where(fn (Builder $inner) => $inner->whereLike('title', "%{$search}%")->orWhereLike('excerpt', "%{$search}%")->orWhereLike('content', "%{$search}%")))
            ->when($status === 'published', fn (Builder $q) => $q->where('is_published', true))
            ->when($status === 'draft', fn (Builder $q) => $q->where('is_published', false))
            ->when(filled($category), fn (Builder $q) => $q->where('category', $category));
    }
}
