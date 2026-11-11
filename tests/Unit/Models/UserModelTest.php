<?php

namespace Tests\Unit\Models;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_helpers(): void
    {
        $admin = User::factory()->admin()->make();
        $member = User::factory()->member()->make();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isMember());
        $this->assertTrue($member->isMember());
        $this->assertFalse($member->isAdmin());
    }

    public function test_profile_photo_url_accessor(): void
    {
        $this->assertSame('/storage/profile-photos/me.png', User::factory()->make(['profile_photo_path' => 'profile-photos/me.png'])->profile_photo_url);
        $this->assertNull(User::factory()->make(['profile_photo_path' => null])->profile_photo_url);
    }

    public function test_posts_relation_and_hidden_attributes(): void
    {
        $user = User::factory()->unverified()->create();
        Post::factory()->count(2)->for($user)->create();

        $this->assertCount(2, $user->posts);
        $this->assertNull($user->email_verified_at);
        $this->assertArrayNotHasKey('password', $user->toArray());
    }
}
