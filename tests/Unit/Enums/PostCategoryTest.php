<?php

namespace Tests\Unit\Enums;

use App\Features\Post\Enums\PostCategory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PostCategoryTest extends TestCase
{
    /**
     * @return array<string, array{PostCategory, string, string}>
     */
    public static function categoryProvider(): array
    {
        return [
            'teknologi' => [PostCategory::TEKNOLOGI, 'Teknologi & Koding', 'bg-blue-100 text-blue-800'],
            'pendidikan' => [PostCategory::PENDIDIKAN, 'Pendidikan & Tutorial', 'bg-emerald-100 text-emerald-800'],
            'gaya hidup' => [PostCategory::GAYA_HIDUP, 'Gaya Hidup & Produktivitas', 'bg-pink-100 text-pink-800'],
            'bisnis' => [PostCategory::BISNIS, 'Bisnis & Karir', 'bg-amber-100 text-amber-900'],
            'opini' => [PostCategory::OPINI, 'Opini & Pemikiran', 'bg-violet-100 text-violet-800'],
            'umum' => [PostCategory::UMUM, 'Umum', 'bg-slate-200 text-slate-800'],
        ];
    }

    #[DataProvider('categoryProvider')]
    public function test_label_and_badge_class(PostCategory $category, string $label, string $badge): void
    {
        $this->assertSame($label, $category->label());
        $this->assertSame($badge, $category->badgeClass());
    }

    public function test_options_and_values(): void
    {
        $options = PostCategory::options();

        $this->assertCount(6, $options);
        $this->assertSame(['value' => 'Teknologi', 'label' => 'Teknologi & Koding', 'badgeClass' => 'bg-blue-100 text-blue-800'], $options[0]);
        $this->assertSame(['Teknologi', 'Pendidikan', 'Gaya Hidup', 'Bisnis', 'Opini', 'Umum'], PostCategory::values());
    }

    public function test_from_loose_matches_value_label_and_falls_back_to_umum(): void
    {
        $this->assertSame(PostCategory::GAYA_HIDUP, PostCategory::fromLoose('  gaya hidup '));
        $this->assertSame(PostCategory::BISNIS, PostCategory::fromLoose('Bisnis & Karir'));
        $this->assertSame(PostCategory::UMUM, PostCategory::fromLoose('Kategori Tidak Dikenal'));
        $this->assertSame(PostCategory::UMUM, PostCategory::fromLoose(null));
    }
}
