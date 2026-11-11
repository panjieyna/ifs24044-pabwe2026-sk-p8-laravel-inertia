<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Pastikan pengguna yang login memiliki peran (role) tertentu.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if ($request->user()?->role !== $role) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk halaman ini.');
        }

        return $next($request);
    }
}
