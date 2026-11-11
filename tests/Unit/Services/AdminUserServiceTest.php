<?php

namespace Tests\Unit\Services;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_list_with_counts_search_and_role_filter(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Utama']);
        $writer = User::factory()->create(['name' => 'Budi Penulis', 'username' => 'budi']);
        Post::factory()->for($writer)->published()->count(2)->create();
        Post::factory()->for($writer)->draft()->create();

        $service = new AdminUserService;

        $all = $service->getUsersList();
        $this->assertSame(2, $all->total());

        $budi = collect($all->items())->firstWhere('id', $writer->id);
        $this->assertSame(3, $budi->posts_count);
        $this->assertSame(2, $budi->published_posts_count);

        $this->assertSame(1, $service->getUsersList('BUDI')->total());
        $this->assertSame($admin->id, $service->getUsersList(null, 'admin')->items()[0]->id);
        $this->assertSame(2, $service->getUsersList(null, 'superuser')->total());
    }

    public function test_user_stats(): void
    {
        User::factory()->admin()->create();
        User::factory()->count(3)->create();

        $this->assertSame(['total' => 4, 'admins' => 1, 'members' => 3], (new AdminUserService)->getUserStats());
    }
}
