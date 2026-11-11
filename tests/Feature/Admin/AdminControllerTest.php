<?php

namespace Tests\Feature\Admin;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create(['name' => 'Admin']);
    }

    public function test_member_cannot_access_admin_pages(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get('/admin/users')->assertForbidden();
        $this->actingAs($member)->get('/admin/posts')->assertForbidden();
        $this->actingAs($member)->get("/admin/users/{$member->id}/posts")->assertForbidden();
    }

    public function test_admin_user_list_with_search_and_role_filter(): void
    {
        User::factory()->create(['name' => 'Citra Penulis']);

        $this->actingAs($this->admin)->get('/admin/users')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/UserList')
                ->has('users.data', 2)
                ->where('stats', ['total' => 2, 'admins' => 1, 'members' => 1]));

        $this->actingAs($this->admin)->get('/admin/users?search=citra&role=member')
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.name', 'Citra Penulis')
                ->where('filters', ['search' => 'citra', 'role' => 'member']));
    }

    public function test_admin_monitors_all_posts_and_exports_them(): void
    {
        Post::factory()->published()->create(['title' => 'Artikel Publik']);
        Post::factory()->draft()->create(['title' => 'Artikel Draf']);

        $this->actingAs($this->admin)->get('/admin/posts?status=draft')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/PostList')
                ->has('posts.data', 1)
                ->where('posts.data.0.title', 'Artikel Draf')
                ->where('filters.status', 'draft'));

        $response = $this->actingAs($this->admin)->get('/admin/posts/export');
        $response->assertOk()->assertDownload();
        $this->assertStringContainsString('semua-artikel-', (string) $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('PK', $response->streamedContent());
    }

    public function test_admin_monitors_and_exports_posts_of_specific_user(): void
    {
        $writer = User::factory()->create(['username' => 'penulis']);
        Post::factory()->for($writer)->published()->count(2)->create();
        Post::factory()->create();

        $this->actingAs($this->admin)->get("/admin/users/{$writer->id}/posts")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/UserPostList')
                ->where('targetUser.id', $writer->id)
                ->has('posts.data', 2)
                ->where('stats', ['total' => 2, 'published' => 2, 'draft' => 0]));

        $response = $this->actingAs($this->admin)->get("/admin/users/{$writer->id}/posts/export");
        $response->assertOk()->assertDownload();
        $this->assertStringContainsString('artikel-penulis-', (string) $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('PK', $response->streamedContent());
    }
}
