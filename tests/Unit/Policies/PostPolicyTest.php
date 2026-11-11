<?php

namespace Tests\Unit\Policies;

use App\Features\Post\Models\Post;
use App\Features\Post\Policies\PostPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_update_and_delete_rules(): void
    {
        $policy = new PostPolicy;
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->for($owner)->create();

        $this->assertTrue($policy->view($owner, $post));
        $this->assertTrue($policy->view($admin, $post));
        $this->assertFalse($policy->view($other, $post));

        $this->assertTrue($policy->update($owner, $post));
        $this->assertFalse($policy->update($admin, $post));

        $this->assertTrue($policy->delete($owner, $post));
        $this->assertFalse($policy->delete($other, $post));
    }
}
