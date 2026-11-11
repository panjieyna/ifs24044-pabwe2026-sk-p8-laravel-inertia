<?php

namespace App\Http\Middleware;

use App\Features\Post\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePostOwnership
{
    /**
     * Penulis hanya boleh mengakses artikelnya sendiri.
     * Admin boleh melihat (GET) artikel siapa pun, tetapi tidak boleh mengubahnya.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $post = $request->route('post');

        if ($user && $post instanceof Post) {
            $isOwner = (int) $user->id === (int) $post->user_id;
            $adminReadOnly = $user->isAdmin() && $request->isMethodSafe();

            if (! $isOwner && ! $adminReadOnly) {
                abort(403, 'Akses ditolak: Anda hanya dapat mengelola artikel milik sendiri.');
            }
        }

        return $next($request);
    }
}
