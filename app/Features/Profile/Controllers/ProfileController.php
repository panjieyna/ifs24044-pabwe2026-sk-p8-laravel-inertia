<?php

namespace App\Features\Profile\Controllers;

use App\Features\Profile\Services\ProfileService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController
{
    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Profile/Edit', [
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'profile_photo_url' => $user->profile_photo_url,
                'posts_count' => $user->posts()->count(),
                'joined_at' => $user->created_at?->translatedFormat('d F Y'),
            ],
        ]);
    }

    public function update(Request $request, ProfileService $service): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $service->updateProfile($user, $validated);

        return back()->with('success', 'Identitas profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request, ProfileService $service): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini yang Anda masukkan salah.',
            ]);
        }

        $service->updatePassword($user, $validated['password']);

        return back()->with('success', 'Password akun berhasil diubah.');
    }

    public function updatePhoto(Request $request, ProfileService $service): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $service->updateProfilePhoto($request->user(), $request->file('photo'));

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function deletePhoto(Request $request, ProfileService $service): RedirectResponse
    {
        $service->deleteProfilePhoto($request->user());

        return back()->with('success', 'Foto profil berhasil dihapus.');
    }
}
