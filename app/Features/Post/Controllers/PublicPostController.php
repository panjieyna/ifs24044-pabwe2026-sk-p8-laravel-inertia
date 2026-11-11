<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Concerns\ResolvesPostFilters;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Services\PostService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPostController
{
    use ResolvesPostFilters;

    /**
     * Beranda & galeri artikel publik (tanpa login).
     */
    public function index(Request $request, PostService $service): Response
    {
        $filters = $this->resolvePostFilters($request);

        return Inertia::render('Public/PostIndex', [
            'posts' => $service->getPublishedPosts($filters['search'], $filters['category'], 9),
            'filters' => ['search' => $filters['search'], 'category' => $filters['category']],
            'categories' => PostCategory::options(),
            'isHome' => $request->routeIs('home'),
        ]);
    }

    /**
     * Halaman baca artikel publik.
     */
    public function show(string $slug, PostService $service): Response
    {
        $post = $service->findPublishedBySlug($slug);

        abort_if($post === null, 404, 'Artikel tidak ditemukan atau belum dipublikasikan.');

        return Inertia::render('Public/PostShow', [
            'post' => $post,
            'related' => $service->getRelatedPosts($post),
            'categories' => PostCategory::options(),
        ]);
    }
}
