<?php

namespace App\Features\Post\Contracts;

use App\Features\Post\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PostRepositoryInterface
{
    /**
     * Artikel milik seorang penulis (dashboard) dengan filter pencarian, status & kategori.
     */
    public function forUser(int $userId, ?string $search = null, ?string $status = null, ?string $category = null, int $perPage = 10): LengthAwarePaginator;

    /**
     * Artikel publik yang sudah dipublikasikan (galeri publik).
     */
    public function publishedPaginated(?string $search = null, ?string $category = null, int $perPage = 9): LengthAwarePaginator;

    /**
     * Cari artikel publik berdasarkan slug.
     */
    public function findPublishedBySlug(string $slug): ?Post;

    /**
     * Artikel publik lain pada kategori yang sama.
     *
     * @return Collection<int, Post>
     */
    public function relatedPublished(Post $post, int $limit = 3): Collection;

    /**
     * Seluruh artikel sistem untuk monitoring admin.
     */
    public function allPaginated(?string $search = null, ?string $status = null, ?string $category = null, int $perPage = 10): LengthAwarePaginator;

    /**
     * Seluruh artikel milik penulis (tanpa paginasi) untuk ekspor.
     *
     * @return Collection<int, Post>
     */
    public function allForUser(int $userId): Collection;

    /**
     * Seluruh artikel sistem (tanpa paginasi) untuk ekspor admin.
     *
     * @return Collection<int, Post>
     */
    public function allWithAuthor(): Collection;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Post;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Post $post, array $attributes): bool;

    public function delete(Post $post): bool;

    /**
     * Cek apakah slug sudah dipakai (opsional mengabaikan id tertentu).
     */
    public function slugExists(string $slug, ?int $ignoreId = null): bool;

    /**
     * Ringkasan statistik artikel penulis.
     *
     * @return array{total: int, published: int, draft: int}
     */
    public function statsForUser(int $userId): array;
}
