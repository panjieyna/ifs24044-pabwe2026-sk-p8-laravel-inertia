<?php

namespace Tests\Unit\Services;

use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Ai\Services\OpenAiPostGeneratorService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class OpenAiPostGeneratorServiceTest extends TestCase
{
    private function fakeCompletion(string $content): void
    {
        Http::fake([
            'api.openai.test/*' => Http::response(['choices' => [['message' => ['content' => $content]]]]),
        ]);
    }

    public function test_constructor_reads_config_and_normalizes_values(): void
    {
        $fromConfig = new OpenAiPostGeneratorService;
        $this->assertSame('https://api.openai.test/v1', $fromConfig->getEndpoint());
        $this->assertSame('test-key', $fromConfig->getApiKey());
        $this->assertSame('gpt-test', $fromConfig->getModel());

        $swapped = new OpenAiPostGeneratorService('sk-rahasia', 'https://proxy.example.com/v1/', '');
        $this->assertSame('https://proxy.example.com/v1', $swapped->getEndpoint());
        $this->assertSame('sk-rahasia', $swapped->getApiKey());
        $this->assertSame('gpt-4o-mini', $swapped->getModel());

        $this->assertSame('https://ai.example.com', (new OpenAiPostGeneratorService('ai.example.com', 'key'))->getEndpoint());
        $this->assertSame(OpenAiPostGeneratorService::DEFAULT_ENDPOINT, (new OpenAiPostGeneratorService('', 'key'))->getEndpoint());
    }

    public function test_generate_posts_sends_request_and_parses_response(): void
    {
        $this->fakeCompletion(json_encode(['posts' => [
            ['title' => 'Tips Belajar Laravel', 'category' => 'Pendidikan', 'excerpt' => 'Ringkas', 'content' => '## Isi'],
            ['title' => 'Artikel Kedua', 'category' => 'Teknologi', 'excerpt' => '', 'content' => 'Isi dua'],
        ]]));

        $items = (new OpenAiPostGeneratorService(timeout: 5))->generatePosts('  Topik belajar  ', 1);

        $this->assertCount(1, $items);
        $this->assertInstanceOf(GeneratedPostItem::class, $items[0]);
        $this->assertSame('Tips Belajar Laravel', $items[0]->title);
        $this->assertSame('Pendidikan', $items[0]->category);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.openai.test/v1/chat/completions'
                && $request->hasHeader('Authorization', 'Bearer test-key')
                && $request['model'] === 'gpt-test'
                && $request['messages'][1]['content'] === 'Topik belajar'
                && str_contains($request['messages'][0]['content'], 'Pilih kategori');
        });
    }

    public function test_generate_posts_forces_requested_category(): void
    {
        $this->fakeCompletion("```json\n[{\"title\":\"Opini Pendek\",\"category\":\"Teknologi\",\"content\":\"Isi\"}]\n```");

        $items = (new OpenAiPostGeneratorService)->generatePosts('Tulis opini', 3, 'Opini');

        $this->assertSame('Opini', $items[0]->category);
        $this->assertNull($items[0]->excerpt);
        Http::assertSent(fn (Request $request) => str_contains($request['messages'][0]['content'], 'WAJIB memakai kategori "Opini"'));
    }

    public function test_generate_posts_throws_on_empty_prompt(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Instruksi artikel tidak boleh kosong.');

        (new OpenAiPostGeneratorService)->generatePosts('   ');
    }

    public function test_generate_posts_throws_on_api_error_with_message(): void
    {
        Http::fake(['*' => Http::response(['error' => ['message' => 'Invalid API key']], 401)]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('OpenAI API Error: Invalid API key');

        (new OpenAiPostGeneratorService)->generatePosts('Topik');
    }

    public function test_generate_posts_throws_on_api_error_without_message(): void
    {
        Http::fake(['*' => Http::response('Server down', 500)]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Request gagal dengan status 500');

        (new OpenAiPostGeneratorService)->generatePosts('Topik');
    }

    public function test_parse_content_accepts_single_object_and_skips_invalid_items(): void
    {
        $service = new OpenAiPostGeneratorService;

        $single = $service->parseContent('{"title":"Satu Artikel","category":"bisnis","excerpt":"Ringkas","content":"Isi"}');
        $this->assertSame('Bisnis', $single[0]->category);

        $filtered = $service->parseContent('{"posts":["teks", {"title":""}, {"title":"Valid"}]}');
        $this->assertCount(1, $filtered);
        $this->assertSame('Valid', $filtered[0]->title);
        $this->assertSame('Umum', $filtered[0]->category);
        $this->assertSame('', $filtered[0]->content);
    }

    public function test_parse_content_throws_on_invalid_json(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('AI mengembalikan format respon yang tidak valid.');

        (new OpenAiPostGeneratorService)->parseContent('bukan json');
    }

    public function test_parse_content_throws_when_no_valid_items(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Tidak ada artikel yang berhasil dirancang oleh AI.');

        (new OpenAiPostGeneratorService)->parseContent('{"posts":[]}');
    }
}
