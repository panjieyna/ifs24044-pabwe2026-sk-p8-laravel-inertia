<?php

namespace App\Features\Post\Controllers;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Ai\Services\OpenAiPostGeneratorService;
use App\Features\Post\Concerns\ResolvesPostFilters;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostExcelService;
use App\Features\Post\Services\PostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PostController
{
    use ResolvesPostFilters;

    public function index(Request $request, PostService $service): Response
    {
        $userId = (int) $request->user()->id;
        $filters = $this->resolvePostFilters($request);

        return Inertia::render('Post/Index', [
            'posts' => $service->getPostsForUser($userId, $filters['search'], $filters['status'], $filters['category'], 10),
            'stats' => $service->getStatsForUser($userId),
            'filters' => $filters,
            'categories' => PostCategory::options(),
            'aiMaxPosts' => OpenAiPostGeneratorService::MAX_POSTS,
        ]);
    }

    public function store(Request $request, PostService $service): RedirectResponse
    {
        $validated = $request->validate([
            ...$this->postRules(),
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $post = $service->createPost((int) $request->user()->id, $validated);

        if ($request->hasFile('cover')) {
            $service->updateCover($post, $request->file('cover'));
        }

        return redirect()->route('dashboard.posts.index')->with('success', 'Artikel baru berhasil disimpan.');
    }

    public function show(Request $request, Post $post): Response
    {
        Gate::authorize('view', $post);

        return Inertia::render('Post/Show', [
            'post' => $post->load('user:id,name,username,profile_photo_path'),
            'canEdit' => $request->user()->can('update', $post),
            'categories' => PostCategory::options(),
        ]);
    }

    public function update(Request $request, Post $post, PostService $service): RedirectResponse
    {
        Gate::authorize('update', $post);

        $service->updatePost($post, $request->validate($this->postRules()));

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Post $post, PostService $service): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $service->deletePost($post);

        return redirect()->route('dashboard.posts.index')->with('success', 'Artikel berhasil dihapus.');
    }

    public function togglePublish(Post $post, PostService $service): RedirectResponse
    {
        Gate::authorize('update', $post);

        $service->togglePublish($post);

        return back()->with('success', $post->is_published ? 'Artikel berhasil dipublikasikan.' : 'Artikel dikembalikan menjadi draf.');
    }

    public function updateCover(Request $request, Post $post, PostService $service): RedirectResponse
    {
        Gate::authorize('update', $post);

        $request->validate([
            'cover' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $service->updateCover($post, $request->file('cover'));

        return back()->with('success', 'Foto sampul berhasil diunggah.');
    }

    public function deleteCover(Post $post, PostService $service): RedirectResponse
    {
        Gate::authorize('update', $post);

        $service->deleteCover($post);

        return back()->with('success', 'Foto sampul berhasil dihapus.');
    }

    public function exportExcel(Request $request, PostService $service, PostExcelService $excel): StreamedResponse
    {
        $user = $request->user();

        return $excel->exportToResponse($service->getAllForUser((int) $user->id), 'artikel-'.$user->username.'-'.now()->format('Ymd-His').'.xlsx');
    }

    public function importExcel(Request $request, PostExcelService $excel): RedirectResponse
    {
        $request->validate([
            'importFile' => ['required', 'file', 'extensions:xlsx,xls,csv', 'max:10240'],
        ]);

        try {
            $count = $excel->import((string) $request->file('importFile')->getRealPath(), (int) $request->user()->id);
        } catch (Throwable) {
            return back()->with('error', 'Berkas tidak dapat dibaca. Pastikan format Excel sesuai template ekspor.');
        }

        return back()->with('success', "Berhasil mengimpor {$count} artikel.");
    }

    public function generateAi(Request $request, AiClientInterface $ai): RedirectResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'min:5', 'max:1000'],
            'count' => ['required', 'integer', 'min:1', 'max:'.OpenAiPostGeneratorService::MAX_POSTS],
            'category' => ['nullable', Rule::enum(PostCategory::class)],
        ]);

        try {
            $items = $ai->generatePosts($validated['prompt'], (int) $validated['count'], $validated['category'] ?? null);
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal merancang artikel dengan AI: '.$e->getMessage());
        }

        return back()->with('aiGeneratedItems', array_map(fn (GeneratedPostItem $item) => $item->toArray(), $items));
    }

    public function saveAi(Request $request, PostService $service): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:'.OpenAiPostGeneratorService::MAX_POSTS],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.category' => ['nullable', 'string', 'max:50'],
            'items.*.excerpt' => ['nullable', 'string', 'max:500'],
            'items.*.content' => ['nullable', 'string', 'max:100000'],
        ]);

        $count = $service->saveGeneratedPosts((int) $request->user()->id, $validated['items']);

        return redirect()->route('dashboard.posts.index')->with('success', "Berhasil menyimpan {$count} draf artikel hasil AI.");
    }

    /**
     * Aturan validasi form artikel.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function postRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(PostCategory::class)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:100000'],
            'is_published' => ['required', 'boolean'],
        ];
    }
}
