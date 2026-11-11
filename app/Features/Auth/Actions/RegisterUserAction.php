<?php

namespace App\Features\Auth\Actions;

use App\Models\User;

class RegisterUserAction
{
    /**
     * Daftarkan pengguna baru (default peran: member/penulis).
     *
     * @param  array{name: string, username: string, email: string, password: string, role?: string}  $data
     */
    public function execute(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'] ?? User::ROLE_MEMBER,
        ]);
    }
}
