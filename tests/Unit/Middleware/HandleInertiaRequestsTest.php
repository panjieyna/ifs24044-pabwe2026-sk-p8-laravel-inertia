<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Tests\TestCase;

class HandleInertiaRequestsTest extends TestCase
{
    private function requestWithSession(?User $user): Request
    {
        $session = new Store('test', new ArraySessionHandler(10));
        $session->put('success', 'Berhasil');
        $session->put('error', 'Gagal');
        $session->put('aiGeneratedItems', [['title' => 'Draf AI']]);

        $request = Request::create('/');
        $request->setLaravelSession($session);
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    public function test_shares_authenticated_user_and_flash_messages(): void
    {
        $user = User::factory()->admin()->make(['id' => 7, 'name' => 'Admin', 'username' => 'admin', 'profile_photo_path' => 'profile-photos/a.png']);

        $shared = (new HandleInertiaRequests)->share($this->requestWithSession($user));

        $this->assertSame('BlogVerse Pro', $shared['appName']);
        $this->assertSame(7, $shared['auth']['user']['id']);
        $this->assertSame('admin', $shared['auth']['user']['role']);
        $this->assertSame('/storage/profile-photos/a.png', $shared['auth']['user']['profile_photo_url']);
        $this->assertSame('Berhasil', $shared['flash']['success']());
        $this->assertSame('Gagal', $shared['flash']['error']());
        $this->assertSame([['title' => 'Draf AI']], $shared['flash']['aiGeneratedItems']());
    }

    public function test_shares_null_user_for_guest(): void
    {
        $shared = (new HandleInertiaRequests)->share($this->requestWithSession(null));

        $this->assertNull($shared['auth']['user']);
    }
}
