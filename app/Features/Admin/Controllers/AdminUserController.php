<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Services\AdminUserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController
{
    /**
     * Kelola daftar pengguna.
     */
    public function index(Request $request, AdminUserService $service): Response
    {
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $role = (string) $request->query('role', '');

        return Inertia::render('Admin/UserList', [
            'users' => $service->getUsersList($search, $role, 10),
            'stats' => $service->getUserStats(),
            'filters' => ['search' => $search, 'role' => $role],
        ]);
    }
}
