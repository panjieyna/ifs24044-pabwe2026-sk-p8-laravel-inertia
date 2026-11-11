<?php

namespace App\Features\Admin\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AdminUserService
{
    /**
     * Daftar pengguna beserta jumlah artikel (total & terbit).
     */
    public function getUsersList(?string $search = null, ?string $role = null, int $perPage = 10): LengthAwarePaginator
    {
        $search = trim((string) $search);

        return User::query()
            ->select(['id', 'name', 'username', 'email', 'role', 'profile_photo_path', 'created_at'])
            ->withCount(['posts', 'posts as published_posts_count' => fn (Builder $q) => $q->where('is_published', true)])
            ->when($search !== '', fn (Builder $q) => $q->where(fn (Builder $inner) => $inner->whereLike('name', "%{$search}%")->orWhereLike('username', "%{$search}%")->orWhereLike('email', "%{$search}%")))
            ->when(in_array($role, [User::ROLE_ADMIN, User::ROLE_MEMBER], true), fn (Builder $q) => $q->where('role', $role))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Ringkasan jumlah pengguna.
     *
     * @return array{total: int, admins: int, members: int}
     */
    public function getUserStats(): array
    {
        $total = User::query()->count();
        $admins = User::query()->where('role', User::ROLE_ADMIN)->count();

        return [
            'total' => $total,
            'admins' => $admins,
            'members' => $total - $admins,
        ];
    }
}
