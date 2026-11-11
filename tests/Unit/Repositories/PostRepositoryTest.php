<?php

namespace Tests\Unit\Repositories;

use App\Features\Post\Models\Post;
use App\Features\Post\Repositories\PostRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PostRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PostRepository;
    }

    public function test_for_user_applies_search_status_and_category_filters(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->published()->create(['title' => 'Belajar Laravel Dasar', 'category' => 'Teknologi']);
        Post::factory()->for($user)->draft()->create(['title' => 'Catatan Produktivitas', 'category' => 'Gaya Hidup', 'content' => 'Membahas laravel sekilas']);
        Post::factory()->published()->create(['title' => 'Laravel milik orang lain']);

        $this->assertSame(2, $this->repository->forUser($user->id)->total());
        $this->assertSame(2, $this->repository->forUser($user->id, 'LARAVEL')->total());
        $this->assertSame(1, $this->repository->forUser($user->id, null, 'published')->total());
        $this->assertSame(1, $this->repository->forUser($user->id, null, 'draft')->total());
        $this->assertSame(1, $this->repository->forUser($user->id, null, null, 'Gaya Hidup')->total());
        $this->assertArrayNotHasKey('content', $this->repository->forUser($user->id)->items()[0]->getAttributes());
    }

    public function test_published_listing_find_by_slug_and_related(): void
    {
        $post = Post::factory()->published()->create(['slug' => 'artikel-utama', 'category' => 'Opini', 'excerpt' => 'Ringkasan opini tajam']);
        Post::factory()->published()->count(2)->create(['category' => 'Opini']);
        Post::factory()->draft()->create(['slug' => 'masih-draf', 'category' => 'Opini']);

        $this->assertSame(3, $this->repository->publishedPaginated()->total());
        $this->assertSame(1, $this->repository->publishedPaginated('opini tajam', 'Opini')->total());

        $found = $this->repository->findPublishedBySlug('artikel-utama');
        $this->assertTrue($found->is($post));
        $this->assertTrue($found->relationLoaded('user'));
        $this->assertNull($this->repository->findPublishedBySlug('masih-draf'));

        $related = $this->repository->relatedPublished($post, 5);
        $this->assertCount(2, $related);
        $this->assertFalse($related->contains('id', $post->id));
    }

    public function test_admin_listing_and_exports(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->published()->create(['title' => 'Bisnis Digital', 'category' => 'Bisnis']);
        Post::factory()->draft()->create();

        $this->assertSame(2, $this->repository->allPaginated()->total());
        $this->assertSame(1, $this->repository->allPaginated('digital', 'published', 'Bisnis')->total());
        $this->assertCount(1, $this->repository->allForUser($user->id));
        $this->assertCount(2, $this->repository->allWithAuthor());
    }

    public function test_crud_slug_check_and_stats(): void
    {
        $user = User::factory()->create();
        $post = $this->repository->create([
            'user_id' => $user->id,
            'title' => 'Judul',
            'slug' => 'judul',
            'category' => 'Umum',
            'content' => 'Isi',
            'is_published' => true,
        ]);

        $this->assertTrue($this->repository->slugExists('judul'));
        $this->assertFalse($this->repository->slugExists('judul', $post->id));
        $this->assertFalse($this->repository->slugExists('tidak-ada'));

        $this->assertTrue($this->repository->update($post, ['title' => 'Judul Baru']));
        $this->assertSame('Judul Baru', $post->fresh()->title);

        Post::factory()->for($user)->draft()->create();
        $this->assertSame(['total' => 2, 'published' => 1, 'draft' => 1], $this->repository->statsForUser($user->id));

        $this->assertTrue($this->repository->delete($post));
        $this->assertModelMissing($post);
    }
}
