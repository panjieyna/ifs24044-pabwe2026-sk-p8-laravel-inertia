<?php

namespace App\Features\Post\Concerns;

use App\Features\Post\Enums\PostCategory;
use Illuminate\Http\Request;

trait ResolvesPostFilters
{
    /**
     * Ambil & sanitasi filter daftar artikel dari query string.
     *
     * @return array{search: string, status: string, category: string}
     */
    protected function resolvePostFilters(Request $request): array
    {
        $status = (string) $request->query('status', '');
        $category = (string) $request->query('category', '');

        return [
            'search' => mb_substr(trim((string) $request->query('search', '')), 0, 100),
            'status' => in_array($status, ['published', 'draft'], true) ? $status : '',
            'category' => in_array($category, PostCategory::values(), true) ? $category : '',
        ];
    }
}
