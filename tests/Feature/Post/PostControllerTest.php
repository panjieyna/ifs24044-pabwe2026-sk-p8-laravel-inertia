<?php

namespace Tests\Feature\Post;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostExcelService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $writer;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->writer = User::factory()->create(['username' => 'penulis']);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Artikel Baru',
            'category' => 'Teknologi',
            'excerpt' => 'Ringkasan singkat.',
            'content' => '## Isi artikel',
            'is_published' => true,
        ], $overrides);
    }

    public function test_index_shows_own_posts_stats_and_filters(): void
    {
        Post::factory()->for($this->writer)->published()->create(['title' => 'Satu']);
        Post::factory()->for($this->writer)->draft()->create(['title' => 'Dua']);
        Post::factory()->create();

        $this->actingAs($this->writer)->get('/dashboard/posts')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Post/Index')
                ->has('posts.data', 2)
                ->where('stats', ['total' => 2, 'published' => 1, 'draft' => 1])
                ->has('categories', 6)
                ->where('aiMaxPosts', 5));

        $this->actingAs($this->writer)->get('/dashboard/posts?status=draft&search=dua')
            ->assertInertia(fn (Assert $page) => $page->has('posts.data', 1)->where('posts.data.0.title', 'Dua'));
    }

    public function test_store_creates_post_with_and_without_cover(): void
    {
        $this->actingAs($this->writer)->post('/dashboard/posts', $this->payload())
            ->assertRedirect('/dashboard/posts')
            ->assertSessionHas('success');

        $post = $this->writer->posts()->firstOrFail();
        $this->assertSame('artikel-baru', $post->slug);
        $this->assertTrue($post->is_published);
        $this->assertNull($post->cover_image_path);

        $this->actingAs($this->writer)->post('/dashboard/posts', $this->payload([
            'title' => 'Artikel Bersampul',
            'is_published' => '0',
            'cover' => UploadedFile::fake()->image('sampul.jpg', 800, 450),
        ]))->assertRedirect('/dashboard/posts');

        $withCover = $this->writer->posts()->where('title', 'Artikel Bersampul')->firstOrFail();
        $this->assertFalse($withCover->is_published);
        Storage::disk('public')->assertExists($withCover->cover_image_path);
    }

    public function test_store_validates_input(): void
    {
        $this->actingAs($this->writer)->post('/dashboard/posts', ['title' => '', 'category' => 'Salah', 'content' => '', 'is_published' => 'mungkin'])
            ->assertSessionHasErrors(['title', 'category', 'content', 'is_published']);
    }

    public function test_show_for_owner_admin_and_other_member(): void
    {
        $post = Post::factory()->for($this->writer)->create();

        $this->actingAs($this->writer)->get("/dashboard/posts/{$post->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Post/Show')
                ->where('post.id', $post->id)
                ->where('post.user.username', 'penulis')
                ->where('canEdit', true));

        $this->actingAs(User::factory()->admin()->create())->get("/dashboard/posts/{$post->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('canEdit', false));

        $this->actingAs(User::factory()->create())->get("/dashboard/posts/{$post->id}")->assertForbidden();
    }

    public function test_update_post(): void
    {
        $post = Post::factory()->for($this->writer)->draft()->create(['title' => 'Lama']);

        $this->actingAs($this->writer)->put("/dashboard/posts/{$post->id}", $this->payload(['title' => 'Judul Diperbarui', 'category' => 'Opini']))
            ->assertRedirect()
            ->assertSessionHas('success', 'Artikel berhasil diperbarui.');

        $post->refresh();
        $this->assertSame('Judul Diperbarui', $post->title);
        $this->assertSame('judul-diperbarui', $post->slug);
        $this->assertTrue($post->is_published);
    }

    public function test_admin_cannot_modify_other_users_post(): void
    {
        $post = Post::factory()->for($this->writer)->create();

        $this->actingAs(User::factory()->admin()->create())->put("/dashboard/posts/{$post->id}", $this->payload())->assertForbidden();
    }

    public function test_toggle_publish_and_destroy(): void
    {
        $post = Post::factory()->for($this->writer)->draft()->create();

        $this->actingAs($this->writer)->patch("/dashboard/posts/{$post->id}/publish")
            ->assertSessionHas('success', 'Artikel berhasil dipublikasikan.');
        $this->assertTrue($post->fresh()->is_published);

        $this->actingAs($this->writer)->patch("/dashboard/posts/{$post->id}/publish")
            ->assertSessionHas('success', 'Artikel dikembalikan menjadi draf.');
        $this->assertFalse($post->fresh()->is_published);

        $this->actingAs($this->writer)->delete("/dashboard/posts/{$post->id}")
            ->assertRedirect('/dashboard/posts')
            ->assertSessionHas('success');
        $this->assertModelMissing($post);
    }

    public function test_cover_upload_and_delete(): void
    {
        $post = Post::factory()->for($this->writer)->create();

        $this->actingAs($this->writer)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('cover.png', 1300, 700)])
            ->assertSessionHas('success', 'Foto sampul berhasil diunggah.');

        $path = $post->fresh()->cover_image_path;
        Storage::disk('public')->assertExists($path);

        $this->actingAs($this->writer)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('cover');

        $this->actingAs($this->writer)->delete("/dashboard/posts/{$post->id}/cover")
            ->assertSessionHas('success', 'Foto sampul berhasil dihapus.');
        Storage::disk('public')->assertMissing($path);
    }

    public function test_export_own_posts_to_excel(): void
    {
        Post::factory()->for($this->writer)->count(2)->create();

        $response = $this->actingAs($this->writer)->get('/dashboard/posts/export');

        $response->assertOk()->assertDownload();
        $this->assertStringContainsString('artikel-penulis-', (string) $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('PK', $response->streamedContent());
    }

    public function test_import_posts_from_excel(): void
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([
            PostExcelService::HEADERS,
            [1, 'Artikel dari Excel', 'Bisnis', 'Ringkasan', 'Isi konten', 'Published'],
        ]);
        $path = sys_get_temp_dir().'/'.uniqid('import', true).'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        $this->actingAs($this->writer)->post('/dashboard/posts/import', ['importFile' => new UploadedFile($path, 'artikel.xlsx', null, null, true)])
            ->assertSessionHas('success', 'Berhasil mengimpor 1 artikel.');

        $this->assertTrue($this->writer->posts()->where('title', 'Artikel dari Excel')->exists());
        @unlink($path);
    }

    public function test_import_rejects_invalid_files(): void
    {
        $this->actingAs($this->writer)->post('/dashboard/posts/import', ['importFile' => UploadedFile::fake()->create('gambar.png', 5)])
            ->assertSessionHasErrors('importFile');

        $this->actingAs($this->writer)->post('/dashboard/posts/import', ['importFile' => UploadedFile::fake()->createWithContent('rusak.xlsx', "\x00\x01\x02\x03 bukan spreadsheet \xff\xfe")])
            ->assertSessionHas('error');
    }

    public function test_ai_generate_returns_items_via_flash(): void
    {
        Http::fake([
            '*' => Http::response(['choices' => [['message' => ['content' => json_encode(['posts' => [
                ['title' => 'Draf AI', 'category' => 'Pendidikan', 'excerpt' => 'Ringkas', 'content' => '## Isi'],
            ]])]]]]),
        ]);

        $this->actingAs($this->writer)->post('/dashboard/posts/ai-generate', ['prompt' => 'Tips belajar efektif', 'count' => 1, 'category' => 'Pendidikan'])
            ->assertRedirect()
            ->assertSessionHas('aiGeneratedItems', [['title' => 'Draf AI', 'category' => 'Pendidikan', 'excerpt' => 'Ringkas', 'content' => '## Isi']]);
    }

    public function test_ai_generate_handles_errors_and_validation(): void
    {
        $this->app->instance(AiClientInterface::class, new class implements AiClientInterface
        {
            public function generatePosts(string $prompt, int $count = 1, ?string $category = null): array
            {
                throw new RuntimeException('Kuota habis');
            }
        });

        $this->actingAs($this->writer)->post('/dashboard/posts/ai-generate', ['prompt' => 'Topik menarik', 'count' => 2])
            ->assertSessionHas('error', 'Gagal merancang artikel dengan AI: Kuota habis');

        $this->actingAs($this->writer)->post('/dashboard/posts/ai-generate', ['prompt' => 'abc', 'count' => 10])
            ->assertSessionHasErrors(['prompt', 'count']);
    }

    public function test_ai_save_stores_generated_items_as_drafts(): void
    {
        $item = new GeneratedPostItem('Draf Tersimpan', 'Opini', null, '## Isi draf');

        $this->actingAs($this->writer)->post('/dashboard/posts/ai-save', ['items' => [$item->toArray()]])
            ->assertRedirect('/dashboard/posts')
            ->assertSessionHas('success', 'Berhasil menyimpan 1 draf artikel hasil AI.');

        $post = $this->writer->posts()->firstOrFail();
        $this->assertSame('Draf Tersimpan', $post->title);
        $this->assertFalse($post->is_published);

        $this->actingAs($this->writer)->post('/dashboard/posts/ai-save', ['items' => []])->assertSessionHasErrors('items');
    }
}
