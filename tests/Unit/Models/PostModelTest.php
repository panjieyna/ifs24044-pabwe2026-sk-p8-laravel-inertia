<?php

namespace Tests\Unit\Models;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PostModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_relation_and_factory_states(): void
    {
        $user = User::factory()->create();
        $published = Post::factory()->published()->for($user)->create();
        $draft = Post::factory()->draft()->for($user)->create();

        $this->assertTrue($published->is_published);
        $this->assertInstanceOf(Carbon::class, $published->published_at);
        $this->assertIsInt($published->user_id);
        $this->assertFalse($draft->is_published);
        $this->assertNull($draft->published_at);
        $this->assertTrue($published->user->is($user));
        $this->assertSame('posts', $published->getTable());
    }

    public function test_cover_url_accessor(): void
    {
        $post = Post::factory()->make(['cover_image_path' => 'post-covers/sample.jpg']);
        $this->assertSame('/storage/post-covers/sample.jpg', $post->cover_url);
        $this->assertArrayHasKey('cover_url', $post->toArray());

        $withoutCover = Post::factory()->make(['cover_image_path' => null]);
        $this->assertNull($withoutCover->cover_url);
    }
}
