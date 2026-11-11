<?php

namespace App\Features\Auth\Controllers;

use App\Features\Auth\Actions\LoginUserAction;
use App\Features\Auth\Actions\RegisterUserAction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController
{
    public function createLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function storeLogin(Request $request, LoginUserAction $action): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ], [
            'login.required' => 'Username atau email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (! $action->execute($credentials['login'], $credentials['password'], (bool) ($credentials['remember'] ?? false))) {
            throw ValidationException::withMessages([
                'login' => 'Username/email atau password yang Anda masukkan tidak sesuai.',
            ]);
        }

        $target = $request->user()?->isAdmin() ? route('admin.users') : route('dashboard.posts.index');

        return redirect()->intended($target)->with('success', 'Selamat datang kembali!');
    }

    public function createRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function storeRegister(Request $request, RegisterUserAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $action->execute([...$validated, 'role' => User::ROLE_MEMBER]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard.posts.index')->with('success', 'Akun penulis berhasil dibuat. Selamat menulis!');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
