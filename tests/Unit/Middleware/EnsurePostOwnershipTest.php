<?php

namespace Tests\Unit\Middleware;

use App\Features\Post\Models\Post;
use App\Http\Middleware\EnsurePostOwnership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsurePostOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private function makeRequest(string $method, ?User $user, mixed $post): Request
    {
        $request = Request::create('/dashboard/posts/1', $method);
        $request->setUserResolver(fn () => $user);

        $route = new Route($method, '/dashboard/posts/{post}', []);
        $route->bind($request);
        $route->setParameter('post', $post);
        $request->setRouteResolver(fn () => $route);

        return $request;
    }

    private function handle(Request $request): string
    {
        return (string) (new EnsurePostOwnership)->handle($request, fn () => new Response('passed'))->getContent();
    }

    public function test_owner_can_access_and_modify(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner)->create();

        $this->assertSame('passed', $this->handle($this->makeRequest('GET', $owner, $post)));
        $this->assertSame('passed', $this->handle($this->makeRequest('PUT', $owner, $post)));
    }

    public function test_admin_can_only_read_other_users_post(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->create();

        $this->assertSame('passed', $this->handle($this->makeRequest('GET', $admin, $post)));

        $this->expectException(HttpException::class);
        $this->handle($this->makeRequest('DELETE', $admin, $post));
    }

    public function test_member_cannot_access_other_users_post(): void
    {
        $member = User::factory()->create();
        $post = Post::factory()->create();

        try {
            $this->handle($this->makeRequest('GET', $member, $post));
            $this->fail('Expected HttpException');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_passes_through_when_route_has_no_bound_post(): void
    {
        $this->assertSame('passed', $this->handle($this->makeRequest('GET', User::factory()->create(), '99')));
    }
}
