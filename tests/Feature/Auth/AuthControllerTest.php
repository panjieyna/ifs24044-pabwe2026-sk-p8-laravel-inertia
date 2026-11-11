<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_register_pages_render_for_guests(): void
    {
        $this->get('/login')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
        $this->get('/register')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Auth/Register'));
    }

    public function test_member_can_login_with_username(): void
    {
        $user = User::factory()->create(['username' => 'member', 'password' => 'member']);

        $this->post('/login', ['login' => 'member', 'password' => 'member', 'remember' => true])
            ->assertRedirect('/dashboard/posts')
            ->assertSessionHas('success');

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_is_redirected_to_admin_panel_and_can_login_with_email(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin@example.com', 'password' => 'admin']);

        $this->post('/login', ['login' => 'admin@example.com', 'password' => 'admin'])->assertRedirect('/admin/users');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_fails_with_wrong_credentials_or_missing_fields(): void
    {
        User::factory()->create(['username' => 'member', 'password' => 'member']);

        $this->from('/login')->post('/login', ['login' => 'member', 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('login');

        $this->post('/login', [])->assertSessionHasErrors(['login', 'password']);
        $this->assertGuest();
    }

    public function test_authenticated_user_is_redirected_away_from_guest_pages(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect('/dashboard/posts');
    }

    public function test_guest_is_redirected_to_login_from_protected_pages(): void
    {
        $this->get('/dashboard/posts')->assertRedirect('/login');
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/admin/users')->assertRedirect('/login');
    }

    public function test_register_creates_member_and_logs_in(): void
    {
        $this->post('/register', [
            'name' => 'Penulis Baru',
            'username' => 'penulis_baru',
            'email' => 'penulis@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/dashboard/posts');

        $user = User::where('username', 'penulis_baru')->firstOrFail();
        $this->assertSame(User::ROLE_MEMBER, $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_register_validates_input(): void
    {
        User::factory()->create(['username' => 'dipakai', 'email' => 'dipakai@example.com']);

        $this->post('/register', [
            'name' => '',
            'username' => 'dipakai',
            'email' => 'dipakai@example.com',
            'password' => 'pendek',
            'password_confirmation' => 'beda',
        ])->assertSessionHasErrors(['name', 'username', 'email', 'password']);

        $this->assertGuest();
    }

    public function test_logout_redirects_to_home(): void
    {
        $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect('/');

        $this->assertGuest();
    }
}
