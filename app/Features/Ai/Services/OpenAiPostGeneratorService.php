<?php

namespace App\Features\Ai\Services;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Enums\PostCategory;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiPostGeneratorService implements AiClientInterface
{
    public const DEFAULT_ENDPOINT = 'https://api.openai.com/v1';

    public const MAX_POSTS = 5;

    protected string $endpoint;

    protected string $apiKey;

    protected string $model;

    protected int $timeout;

    public function __construct(?string $endpoint = null, ?string $apiKey = null, ?string $model = null, ?int $timeout = null)
    {
        $resolvedEndpoint = trim((string) ($endpoint ?? config('openai.endpoint')));
        $resolvedKey = trim((string) ($apiKey ?? config('openai.key')));

        // Toleransi jika nilai OPENAI_ENDPOINT dan OPENAI_KEY tertukar di .env.
        if ($this->looksLikeUrl($resolvedKey) && ! $this->looksLikeUrl($resolvedEndpoint)) {
            [$resolvedEndpoint, $resolvedKey] = [$resolvedKey, $resolvedEndpoint];
        }

        $resolvedEndpoint = $resolvedEndpoint !== '' ? $resolvedEndpoint : self::DEFAULT_ENDPOINT;
        $resolvedEndpoint = $this->looksLikeUrl($resolvedEndpoint) ? $resolvedEndpoint : 'https://'.$resolvedEndpoint;

        $this->endpoint = rtrim($resolvedEndpoint, '/');
        $this->apiKey = $resolvedKey;
        $this->model = trim((string) ($model ?? config('openai.model'))) ?: 'gpt-4o-mini';
        $this->timeout = max(10, (int) ($timeout ?? config('openai.timeout', 60)));
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Rancang draf artikel menggunakan OpenAI Chat Completions.
     *
     * @return array<int, GeneratedPostItem>
     *
     * @throws RuntimeException
     */
    public function generatePosts(string $prompt, int $count = 1, ?string $category = null): array
    {
        $prompt = trim($prompt);
        if ($prompt === '') {
            throw new RuntimeException('Instruksi artikel tidak boleh kosong.');
        }

        $count = min(self::MAX_POSTS, max(1, $count));
        $categories = implode(', ', PostCategory::values());
        $categoryRule = $category ? "Semua artikel WAJIB memakai kategori \"{$category}\"." : "Pilih kategori yang paling sesuai dari: {$categories}.";

        $systemPrompt = <<<SYS
Anda adalah editor blog profesional berbahasa Indonesia. Tulis tepat {$count} draf artikel blog yang informatif, orisinal, dan rapi berdasarkan permintaan pengguna.
{$categoryRule}

Balas HANYA dengan JSON valid sesuai skema berikut tanpa teks lain:
{
  "posts": [
    {
      "title": "Judul artikel yang menarik (maks. 120 karakter)",
      "category": "Salah satu dari: {$categories}",
      "excerpt": "Ringkasan pemikat pembaca 1-2 kalimat",
      "content": "Isi artikel lengkap dalam format Markdown (gunakan subjudul ##, paragraf, dan daftar bila perlu)"
    }
  ]
}
SYS;

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->timeout($this->timeout)
            ->post("{$this->endpoint}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
            ]);

        if (! $response->successful()) {
            $message = $response->json('error.message') ?? "Request gagal dengan status {$response->status()}";

            throw new RuntimeException("OpenAI API Error: {$message}");
        }

        return array_slice($this->parseContent((string) $response->json('choices.0.message.content'), $category), 0, $count);
    }

    /**
     * Ubah konten balasan AI (JSON, boleh dibungkus code fence) menjadi daftar DTO.
     *
     * @return array<int, GeneratedPostItem>
     *
     * @throws RuntimeException
     */
    public function parseContent(string $rawContent, ?string $forcedCategory = null): array
    {
        $clean = trim($rawContent);
        $clean = trim((string) preg_replace(['/^```(?:json)?\s*/i', '/\s*```$/'], '', $clean));

        $decoded = json_decode($clean, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('AI mengembalikan format respon yang tidak valid.');
        }

        $items = isset($decoded['posts']) && is_array($decoded['posts']) ? $decoded['posts'] : (array_is_list($decoded) ? $decoded : [$decoded]);

        $results = [];
        foreach ($items as $item) {
            if (! is_array($item) || trim((string) ($item['title'] ?? '')) === '') {
                continue;
            }

            $excerpt = trim((string) ($item['excerpt'] ?? ''));

            $results[] = new GeneratedPostItem(
                title: mb_substr(trim((string) $item['title']), 0, 255),
                category: PostCategory::fromLoose($forcedCategory ?? (string) ($item['category'] ?? ''))->value,
                excerpt: $excerpt !== '' ? $excerpt : null,
                content: trim((string) ($item['content'] ?? '')),
            );
        }

        if ($results === []) {
            throw new RuntimeException('Tidak ada artikel yang berhasil dirancang oleh AI.');
        }

        return $results;
    }

    protected function looksLikeUrl(string $value): bool
    {
        return str_starts_with($value, 'http://') || str_starts_with($value, 'https://');
    }
}
