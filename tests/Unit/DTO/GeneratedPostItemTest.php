<?php

namespace Tests\Unit\DTO;

use App\Features\Ai\DTO\GeneratedPostItem;
use PHPUnit\Framework\TestCase;

class GeneratedPostItemTest extends TestCase
{
    public function test_to_array(): void
    {
        $item = new GeneratedPostItem('Judul', 'Teknologi', 'Ringkasan', '## Isi');

        $this->assertSame([
            'title' => 'Judul',
            'category' => 'Teknologi',
            'excerpt' => 'Ringkasan',
            'content' => '## Isi',
        ], $item->toArray());
    }
}
