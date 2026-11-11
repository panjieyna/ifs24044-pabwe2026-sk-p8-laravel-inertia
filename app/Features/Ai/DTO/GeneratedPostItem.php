<?php

namespace App\Features\Ai\DTO;

class GeneratedPostItem
{
    public function __construct(
        public string $title,
        public string $category,
        public ?string $excerpt,
        public string $content,
    ) {}

    /**
     * Konversi DTO menjadi array untuk dikirim ke frontend / disimpan.
     *
     * @return array{title: string, category: string, excerpt: string|null, content: string}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'category' => $this->category,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
        ];
    }
}
