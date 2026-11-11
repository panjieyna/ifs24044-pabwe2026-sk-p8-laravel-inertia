<?php

namespace Tests\Feature\Public;

use App\Features\Post\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicPostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_public_and_lists_only_published_posts(): void
    {
        Post::factory()->published()->count(2)->create();
        Post::factory()->draft()->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/PostIndex')
                ->where('isHome', true)
                ->where('auth.user', null)
                ->has('posts.data', 2)
                ->has('categories', 6)
                ->where('filters', ['search' => '', 'category' => '']));
    }

    public function test_articles_page_supports_search_and_category_filter(): void
    {
        Post::factory()->published()->create(['title' => 'Belajar React Hooks', 'category' => 'Teknologi']);
        Post::factory()->published()->create(['title' => 'Strategi Bisnis Kecil', 'category' => 'Bisnis']);

        $this->get('/articles?search=react&category=Teknologi&status=draft')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/PostIndex')
                ->where('isHome', false)
                ->where('filters', ['search' => 'react', 'category' => 'Teknologi'])
                ->has('posts.data', 1)
                ->where('posts.data.0.title', 'Belajar React Hooks'));

        $this->get('/articles?category=TidakAda')
            ->assertInertia(fn (Assert $page) => $page->where('filters.category', '')->has('posts.data', 2));
    }

    public function test_article_page_shows_published_post_with_related_posts(): void
    {
        $post = Post::factory()->published()->create(['slug' => 'artikel-publik', 'category' => 'Opini']);
        Post::factory()->published()->create(['category' => 'Opini']);

        $this->get('/articles/artikel-publik')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/PostShow')
                ->where('post.id', $post->id)
                ->where('post.content', $post->content)
                ->where('post.user.id', $post->user_id)
                ->has('related', 1));
    }

    public function test_draft_or_unknown_article_returns_404(): void
    {
        Post::factory()->draft()->create(['slug' => 'draf-rahasia']);

        $this->get('/articles/draf-rahasia')->assertNotFound();
        $this->get('/articles/tidak-ada')->assertNotFound();
    }
}
