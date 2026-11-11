<?php

namespace App\Features\Post\Services;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostService
{
    /**
     * Lebar maksimum gambar sampul (px). Gambar lebih besar akan diperkecil.
     */
    public const COVER_MAX_WIDTH = 1200;

    public function __construct(
        protected PostRepositoryInterface $posts
    ) {}

    /**
     * Daftar artikel milik penulis untuk dashboard.
     */
    public function getPostsForUser(int $userId, ?string $search = null, ?string $status = null, ?string $category = null, int $perPage = 10): LengthAwarePaginator
    {
        return $this->posts->forUser($userId, $search, $status, $category, $perPage);
    }

    /**
     * Ringkasan statistik artikel penulis.
     *
     * @return array{total: int, published: int, draft: int}
     */
    public function getStatsForUser(int $userId): array
    {
        return $this->posts->statsForUser($userId);
    }

    /**
     * Galeri artikel publik.
     */
    public function getPublishedPosts(?string $search = null, ?string $category = null, int $perPage = 9): LengthAwarePaginator
    {
        return $this->posts->publishedPaginated($search, $category, $perPage);
    }

    /**
     * Artikel publik berdasarkan slug.
     */
    public function findPublishedBySlug(string $slug): ?Post
    {
        return $this->posts->findPublishedBySlug($slug);
    }

    /**
     * Artikel terkait (kategori sama).
     *
     * @return Collection<int, Post>
     */
    public function getRelatedPosts(Post $post, int $limit = 3): Collection
    {
        return $this->posts->relatedPublished($post, $limit);
    }

    /**
     * Seluruh artikel sistem untuk admin.
     */
    public function getAllPosts(?string $search = null, ?string $status = null, ?string $category = null, int $perPage = 10): LengthAwarePaginator
    {
        return $this->posts->allPaginated($search, $status, $category, $perPage);
    }

    /**
     * Seluruh artikel sistem beserta penulis (ekspor admin).
     *
     * @return Collection<int, Post>
     */
    public function getAllWithAuthor(): Collection
    {
        return $this->posts->allWithAuthor();
    }

    /**
     * Seluruh artikel milik penulis (ekspor).
     *
     * @return Collection<int, Post>
     */
    public function getAllForUser(int $userId): Collection
    {
        return $this->posts->allForUser($userId);
    }

    /**
     * Buat artikel baru.
     *
     * @param  array<string, mixed>  $data
     */
    public function createPost(int $userId, array $data): Post
    {
        $isPublished = (bool) ($data['is_published'] ?? false);
        $content = (string) ($data['content'] ?? '');

        return $this->posts->create([
            'user_id' => $userId,
            'title' => (string) $data['title'],
            'slug' => $this->generateUniqueSlug((string) $data['title']),
            'category' => PostCategory::fromLoose($data['category'] ?? null)->value,
            'excerpt' => $this->resolveExcerpt($data['excerpt'] ?? null, $content),
            'content' => $content,
            'is_published' => $isPublished,
            'published_at' => $isPublished ? Carbon::now() : null,
        ]);
    }

    /**
     * Perbarui artikel. Slug dibuat ulang hanya jika judul berubah.
     *
     * @param  array{title: string, category: string, excerpt?: string|null, content: string, is_published: bool}  $data
     */
    public function updatePost(Post $post, array $data): bool
    {
        $isPublished = (bool) $data['is_published'];
        $slug = $data['title'] === $post->title ? $post->slug : $this->generateUniqueSlug($data['title'], $post->id);

        return $this->posts->update($post, [
            'title' => $data['title'],
            'slug' => $slug,
            'category' => PostCategory::fromLoose($data['category'])->value,
            'excerpt' => $this->resolveExcerpt($data['excerpt'] ?? null, $data['content']),
            'content' => $data['content'],
            'is_published' => $isPublished,
            'published_at' => $this->resolvePublishedAt($post, $isPublished),
        ]);
    }

    /**
     * Toggle status publikasi (Draft <-> Published).
     */
    public function togglePublish(Post $post): bool
    {
        $isPublished = ! $post->is_published;

        return $this->posts->update($post, [
            'is_published' => $isPublished,
            'published_at' => $this->resolvePublishedAt($post, $isPublished),
        ]);
    }

    /**
     * Hapus artikel beserta gambar sampulnya.
     */
    public function deletePost(Post $post): bool
    {
        $this->deleteCoverFile($post->cover_image_path);

        return $this->posts->delete($post);
    }

    /**
     * Unggah / ganti gambar sampul artikel.
     */
    public function updateCover(Post $post, UploadedFile $file): string
    {
        $path = $this->storeCoverImage($file);
        $this->deleteCoverFile($post->cover_image_path);
        $this->posts->update($post, ['cover_image_path' => $path]);

        return $path;
    }

    /**
     * Hapus gambar sampul artikel.
     */
    public function deleteCover(Post $post): bool
    {
        $this->deleteCoverFile($post->cover_image_path);

        return $this->posts->update($post, ['cover_image_path' => null]);
    }

    /**
     * Simpan artikel hasil AI sebagai draf.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    public function saveGeneratedPosts(int $userId, array $items): int
    {
        $saved = 0;

        foreach ($items as $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $this->createPost($userId, [
                'title' => mb_substr($title, 0, 255),
                'category' => $item['category'] ?? null,
                'excerpt' => $item['excerpt'] ?? null,
                'content' => (string) ($item['content'] ?? ''),
                'is_published' => false,
            ]);
            $saved++;
        }

        return $saved;
    }

    /**
     * Buat slug unik dari judul.
     */
    public function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = trim(mb_substr(Str::slug($title), 0, 200), '-');
        $base = $base !== '' ? $base : 'artikel';
        $slug = $base;
        $counter = 2;

        while ($this->posts->slugExists($slug, $ignoreId)) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Gunakan ringkasan dari pengguna, atau buat otomatis dari konten Markdown.
     */
    public function resolveExcerpt(?string $excerpt, string $content): ?string
    {
        $excerpt = trim((string) $excerpt);
        if ($excerpt !== '') {
            return Str::limit($excerpt, 300);
        }

        $plain = trim((string) preg_replace('/\s+/', ' ', strip_tags(Str::markdown($content))));

        return $plain === '' ? null : Str::limit($plain, 160);
    }

    /**
     * Waktu publikasi: dipertahankan bila sudah ada, diisi sekarang bila baru dipublikasikan, null bila draf.
     */
    protected function resolvePublishedAt(Post $post, bool $isPublished): ?Carbon
    {
        return $isPublished ? ($post->published_at ?? Carbon::now()) : null;
    }

    /**
     * Simpan gambar sampul sebagai JPEG terkompresi (maks. 1200px) agar halaman tetap ringan.
     */
    protected function storeCoverImage(UploadedFile $file): string
    {
        $image = @imagecreatefromstring((string) file_get_contents((string) $file->getRealPath()));
        if ($image === false) {
            return (string) $file->store('post-covers', 'public');
        }

        if (imagesx($image) > self::COVER_MAX_WIDTH) {
            $image = imagescale($image, self::COVER_MAX_WIDTH) ?: $image;
        }

        $canvas = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($canvas, 0, 0, (int) imagecolorallocate($canvas, 255, 255, 255));
        imagecopy($canvas, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

        ob_start();
        imagejpeg($canvas, null, 82);
        $jpeg = (string) ob_get_clean();

        $path = 'post-covers/'.Str::random(40).'.jpg';
        Storage::disk('public')->put($path, $jpeg);

        return $path;
    }

    /**
     * Hapus berkas sampul lama bila ada.
     */
    protected function deleteCoverFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
