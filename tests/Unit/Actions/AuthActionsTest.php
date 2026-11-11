<?php

namespace Tests\Unit\Actions;

use App\Features\Auth\Actions\LoginUserAction;
use App\Features\Auth\Actions\RegisterUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_action_creates_member_with_hashed_password(): void
    {
        $user = (new RegisterUserAction)->execute([
            'name' => 'Penulis Baru',
            'username' => 'penulisbaru',
            'email' => 'baru@example.com',
            'password' => 'rahasia123',
        ]);

        $this->assertSame(User::ROLE_MEMBER, $user->role);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_login_action_with_username_email_and_wrong_password(): void
    {
        User::factory()->create(['username' => 'member', 'email' => 'member@example.com', 'password' => 'member']);
        $action = new LoginUserAction;

        $this->assertFalse($action->execute('member', 'salah'));
        $this->assertGuest();

        $this->assertTrue($action->execute('member', 'member'));
        $this->assertAuthenticated();
        Auth::logout();

        $this->assertTrue($action->execute('member@example.com', 'member', true));
        $this->assertAuthenticated();
    }
}
