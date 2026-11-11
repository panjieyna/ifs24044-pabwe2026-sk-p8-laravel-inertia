<?php

namespace Tests\Unit\Services;

use App\Features\Profile\Services\ProfileService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_profile_and_password(): void
    {
        $user = User::factory()->create();
        $service = new ProfileService;

        $this->assertTrue($service->updateProfile($user, ['name' => 'Nama Baru', 'username' => 'namabaru', 'email' => 'baru@example.com']));
        $this->assertSame('namabaru', $user->fresh()->username);

        $this->assertTrue($service->updatePassword($user, 'passwordbaru'));
        $this->assertTrue(Hash::check('passwordbaru', $user->fresh()->password));
    }

    public function test_upload_replaces_and_deletes_profile_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $service = new ProfileService;

        $first = $service->updateProfilePhoto($user, UploadedFile::fake()->image('a.png'));
        Storage::disk('public')->assertExists($first);

        $second = $service->updateProfilePhoto($user->fresh(), UploadedFile::fake()->image('b.png'));
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        $this->assertTrue($service->deleteProfilePhoto($user->fresh()));
        Storage::disk('public')->assertMissing($second);
        $this->assertNull($user->fresh()->profile_photo_path);

        // Menghapus ketika tidak ada foto tetap aman.
        $this->assertTrue($service->deleteProfilePhoto($user->fresh()));
    }
}
