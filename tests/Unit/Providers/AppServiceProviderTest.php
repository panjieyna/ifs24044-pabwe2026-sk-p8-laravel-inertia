<?php

namespace Tests\Unit\Providers;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\Services\OpenAiPostGeneratorService;
use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Models\Post;
use App\Features\Post\Policies\PostPolicy;
use App\Features\Post\Repositories\PostRepository;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    public function test_container_bindings_and_policy(): void
    {
        $this->assertInstanceOf(PostRepository::class, app(PostRepositoryInterface::class));
        $this->assertInstanceOf(OpenAiPostGeneratorService::class, app(AiClientInterface::class));
        $this->assertInstanceOf(PostPolicy::class, Gate::getPolicyFor(Post::class));
    }

    public function test_force_https_when_configured(): void
    {
        config(['app.force_https' => true]);

        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('https://', URL::to('/articles'));

        URL::forceScheme('http');
        config(['app.force_https' => false]);
    }

    public function test_web_rate_limiter_uses_user_or_ip(): void
    {
        $limiter = RateLimiter::limiter('web');

        $guestRequest = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '10.0.0.1']);
        $guestLimit = $limiter($guestRequest);
        $this->assertSame(1000, $guestLimit->maxAttempts);
        $this->assertSame('10.0.0.1', $guestLimit->key);

        $userRequest = Request::create('/');
        $userRequest->setUserResolver(fn () => User::factory()->make(['id' => 42]));
        $this->assertSame(42, $limiter($userRequest)->key);
    }
}
