<?php

namespace Tests\Unit\Services;

use App\Features\Post\Models\Post;
use App\Features\Post\Repositories\PostRepository;
use App\Features\Post\Services\PostService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    private PostService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = new PostService(new PostRepository);
    }

    public function test_create_post_generates_unique_slug_excerpt_and_publication_time(): void
    {
        $user = User::factory()->create();

        $published = $this->service->createPost($user->id, [
            'title' => 'Halo Dunia',
            'category' => 'teknologi',
            'excerpt' => '',
            'content' => "## Pembuka\n\nIni **isi** artikel pertama.",
            'is_published' => true,
        ]);

        $this->assertSame('halo-dunia', $published->slug);
        $this->assertSame('Teknologi', $published->category);
        $this->assertSame('Pembuka Ini isi artikel pertama.', $published->excerpt);
        $this->assertNotNull($published->published_at);

        $draft = $this->service->createPost($user->id, ['title' => 'Halo Dunia', 'excerpt' => 'Ringkasan manual']);

        $this->assertSame('halo-dunia-2', $draft->slug);
        $this->assertSame('Umum', $draft->category);
        $this->assertSame('Ringkasan manual', $draft->excerpt);
        $this->assertSame('', $draft->content);
        $this->assertFalse($draft->is_published);
        $this->assertNull($draft->published_at);
    }

    public function test_generate_unique_slug_and_resolve_excerpt_edge_cases(): void
    {
        $this->assertSame('artikel', $this->service->generateUniqueSlug('!!!'));
        $this->assertNull($this->service->resolveExcerpt(null, ''));
        $auto = (string) $this->service->resolveExcerpt(null, str_repeat('kata ', 100));
        $this->assertStringEndsWith('...', $auto);
        $this->assertLessThanOrEqual(163, mb_strlen($auto));
        $this->assertSame(303, mb_strlen((string) $this->service->resolveExcerpt(str_repeat('a', 400), 'isi')));
    }

    public function test_update_post_keeps_slug_when_title_unchanged_and_regenerates_otherwise(): void
    {
        $post = Post::factory()->draft()->create(['title' => 'Judul Lama', 'slug' => 'judul-lama']);

        $this->service->updatePost($post, ['title' => 'Judul Lama', 'category' => 'Opini', 'excerpt' => null, 'content' => 'Isi baru', 'is_published' => true]);
        $post->refresh();
        $this->assertSame('judul-lama', $post->slug);
        $this->assertSame('Opini', $post->category);
        $this->assertTrue($post->is_published);
        $firstPublishedAt = $post->published_at;
        $this->assertNotNull($firstPublishedAt);

        Carbon::setTestNow(now()->addDay());
        $this->service->updatePost($post, ['title' => 'Judul Baru', 'category' => 'Opini', 'content' => 'Isi', 'is_published' => true]);
        $post->refresh();
        $this->assertSame('judul-baru', $post->slug);
        $this->assertTrue($firstPublishedAt->equalTo($post->published_at));
        Carbon::setTestNow();

        $this->service->updatePost($post, ['title' => 'Judul Baru', 'category' => 'Opini', 'content' => 'Isi', 'is_published' => false]);
        $this->assertNull($post->fresh()->published_at);
    }

    public function test_toggle_publish_switches_status(): void
    {
        $post = Post::factory()->draft()->create();

        $this->service->togglePublish($post);
        $this->assertTrue($post->fresh()->is_published);
        $this->assertNotNull($post->fresh()->published_at);

        $this->service->togglePublish($post->fresh());
        $this->assertFalse($post->fresh()->is_published);
        $this->assertNull($post->fresh()->published_at);
    }

    public function test_cover_upload_resizes_replaces_and_deletes(): void
    {
        $post = Post::factory()->create();

        $firstPath = $this->service->updateCover($post, UploadedFile::fake()->image('wide.png', 1600, 900));
        Storage::disk('public')->assertExists($firstPath);
        $this->assertStringEndsWith('.jpg', $firstPath);
        [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($firstPath));
        $this->assertSame(PostService::COVER_MAX_WIDTH, $width);
        $this->assertSame(675, $height);

        $secondPath = $this->service->updateCover($post->fresh(), UploadedFile::fake()->image('small.jpg', 400, 300));
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);
        $this->assertSame([400, 300], array_slice(getimagesizefromstring(Storage::disk('public')->get($secondPath)), 0, 2));

        $this->assertTrue($this->service->deleteCover($post->fresh()));
        Storage::disk('public')->assertMissing($secondPath);
        $this->assertNull($post->fresh()->cover_image_path);
    }

    public function test_cover_upload_falls_back_to_original_file_when_not_decodable(): void
    {
        $post = Post::factory()->create();

        $path = $this->service->updateCover($post, UploadedFile::fake()->createWithContent('cover.webp', 'bukan-gambar'));

        $this->assertStringStartsWith('post-covers/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_delete_post_removes_cover_file(): void
    {
        Storage::disk('public')->put('post-covers/old.jpg', 'x');
        $post = Post::factory()->create(['cover_image_path' => 'post-covers/old.jpg']);

        $this->assertTrue($this->service->deletePost($post));
        Storage::disk('public')->assertMissing('post-covers/old.jpg');
        $this->assertModelMissing($post);
    }

    public function test_save_generated_posts_skips_items_without_title(): void
    {
        $user = User::factory()->create();

        $saved = $this->service->saveGeneratedPosts($user->id, [
            ['title' => 'Draf AI Pertama', 'category' => 'Bisnis', 'excerpt' => 'Ringkas', 'content' => '## Isi'],
            ['title' => '   '],
            ['title' => 'Draf AI Kedua'],
        ]);

        $this->assertSame(2, $saved);
        $this->assertSame(2, $user->posts()->where('is_published', false)->count());
        $this->assertSame('Bisnis', $user->posts()->where('title', 'Draf AI Pertama')->value('category'));
    }

    public function test_read_methods_delegate_to_repository(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->published()->create(['slug' => 'publik', 'category' => 'Umum']);
        Post::factory()->published()->create(['category' => 'Umum']);
        Post::factory()->for($user)->draft()->create();

        $this->assertSame(2, $this->service->getPostsForUser($user->id)->total());
        $this->assertSame(['total' => 2, 'published' => 1, 'draft' => 1], $this->service->getStatsForUser($user->id));
        $this->assertSame(2, $this->service->getPublishedPosts()->total());
        $this->assertTrue($this->service->findPublishedBySlug('publik')->is($post));
        $this->assertCount(1, $this->service->getRelatedPosts($post));
        $this->assertSame(3, $this->service->getAllPosts()->total());
        $this->assertCount(3, $this->service->getAllWithAuthor());
        $this->assertCount(2, $this->service->getAllForUser($user->id));
    }
}
