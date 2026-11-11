<?php

namespace Tests\Feature\Profile;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_shows_user_data(): void
    {
        $user = User::factory()->create(['name' => 'Penulis Satu']);
        Post::factory()->for($user)->count(2)->create();

        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Edit')
                ->where('profile.name', 'Penulis Satu')
                ->where('profile.posts_count', 2)
                ->where('auth.user.id', $user->id));
    }

    public function test_update_profile_identity(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', ['name' => 'Nama Baru', 'username' => 'nama_baru', 'email' => 'nama@example.com'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('nama_baru', $user->fresh()->username);

        $this->actingAs($user)->patch('/profile', ['name' => '', 'username' => 'a', 'email' => 'bukan-email'])
            ->assertSessionHasErrors(['name', 'username', 'email']);
    }

    public function test_update_password_requires_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => 'lama12345']);

        $this->actingAs($user)->put('/profile/password', ['current_password' => 'salah', 'password' => 'baru12345', 'password_confirmation' => 'baru12345'])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put('/profile/password', ['current_password' => 'lama12345', 'password' => 'baru12345', 'password_confirmation' => 'baru12345'])
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('baru12345', $user->fresh()->password));
    }

    public function test_upload_and_delete_profile_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/profile/photo', ['photo' => UploadedFile::fake()->image('avatar.png', 200, 200)])
            ->assertSessionHas('success');

        $path = $user->fresh()->profile_photo_path;
        Storage::disk('public')->assertExists($path);

        $this->actingAs($user)->post('/profile/photo', [])->assertSessionHasErrors('photo');

        $this->actingAs($user)->delete('/profile/photo')->assertSessionHas('success');
        Storage::disk('public')->assertMissing($path);
        $this->assertNull($user->fresh()->profile_photo_path);
    }
}
