<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\EnsureRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureRoleTest extends TestCase
{
    private function requestAs(?User $user): Request
    {
        $request = Request::create('/admin/users', 'GET');
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    public function test_allows_user_with_matching_role(): void
    {
        $response = (new EnsureRole)->handle($this->requestAs(User::factory()->admin()->make()), fn () => new Response('ok'), 'admin');

        $this->assertSame('ok', $response->getContent());
    }

    public function test_rejects_user_with_other_role(): void
    {
        $this->expectException(HttpException::class);

        (new EnsureRole)->handle($this->requestAs(User::factory()->member()->make()), fn () => new Response('ok'), 'admin');
    }

    public function test_rejects_guest(): void
    {
        try {
            (new EnsureRole)->handle($this->requestAs(null), fn () => new Response('ok'), 'admin');
            $this->fail('Expected HttpException');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }
}
