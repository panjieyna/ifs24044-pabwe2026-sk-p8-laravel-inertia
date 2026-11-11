<?php

namespace App\Features\Ai\Contracts;

use App\Features\Ai\DTO\GeneratedPostItem;

interface AiClientInterface
{
    /**
     * Rancang beberapa draf artikel berdasarkan instruksi (prompt) pengguna.
     *
     * @return array<int, GeneratedPostItem>
     */
    public function generatePosts(string $prompt, int $count = 1, ?string $category = null): array;
}
