<?php

namespace App\Features\Profile\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    /**
     * Perbarui identitas profil.
     *
     * @param  array{name: string, username: string, email: string}  $data
     */
    public function updateProfile(User $user, array $data): bool
    {
        return $user->update([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
        ]);
    }

    /**
     * Perbarui password (otomatis di-hash oleh cast "hashed").
     */
    public function updatePassword(User $user, string $newPassword): bool
    {
        return $user->update(['password' => $newPassword]);
    }

    /**
     * Unggah foto profil baru dan hapus foto lama.
     */
    public function updateProfilePhoto(User $user, UploadedFile $photo): string
    {
        $path = (string) $photo->store('profile-photos', 'public');
        $this->deleteProfilePhoto($user);
        $user->update(['profile_photo_path' => $path]);

        return $path;
    }

    /**
     * Hapus foto profil.
     */
    public function deleteProfilePhoto(User $user): bool
    {
        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        return $user->update(['profile_photo_path' => null]);
    }
}
