<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Concerns\ResolvesPostFilters;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Services\PostExcelService;
use App\Features\Post\Services\PostService;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminPostController
{
    use ResolvesPostFilters;

    /**
     * Monitoring seluruh artikel sistem (read-only).
     */
    public function index(Request $request, PostService $service): Response
    {
        $filters = $this->resolvePostFilters($request);

        return Inertia::render('Admin/PostList', [
            'posts' => $service->getAllPosts($filters['search'], $filters['status'], $filters['category'], 10),
            'filters' => $filters,
            'categories' => PostCategory::options(),
        ]);
    }

    /**
     * Ekspor seluruh artikel sistem ke Excel.
     */
    public function export(PostService $service, PostExcelService $excel): StreamedResponse
    {
        return $excel->exportToResponse($service->getAllWithAuthor(), 'semua-artikel-'.now()->format('Ymd-His').'.xlsx');
    }

    /**
     * Monitoring artikel milik pengguna tertentu (read-only).
     */
    public function userPosts(Request $request, User $user, PostService $service): Response
    {
        $filters = $this->resolvePostFilters($request);

        return Inertia::render('Admin/UserPostList', [
            'targetUser' => $user->only(['id', 'name', 'username', 'email', 'role']),
            'posts' => $service->getPostsForUser($user->id, $filters['search'], $filters['status'], $filters['category'], 10),
            'stats' => $service->getStatsForUser($user->id),
            'filters' => $filters,
            'categories' => PostCategory::options(),
        ]);
    }

    /**
     * Ekspor artikel milik pengguna tertentu ke Excel.
     */
    public function exportUserPosts(User $user, PostService $service, PostExcelService $excel): StreamedResponse
    {
        return $excel->exportToResponse($service->getAllForUser($user->id), 'artikel-'.$user->username.'-'.now()->format('Ymd-His').'.xlsx');
    }
}
