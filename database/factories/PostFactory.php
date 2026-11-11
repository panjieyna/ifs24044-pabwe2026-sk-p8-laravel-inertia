<?php

namespace Database\Factories;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Post>
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(5), '.');

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(5)),
            'category' => fake()->randomElement(PostCategory::values()),
            'excerpt' => fake()->sentence(14),
            'content' => "## Pendahuluan\n\n".fake()->paragraph(4)."\n\n## Pembahasan\n\n".fake()->paragraph(5),
            'cover_image_path' => null,
            'is_published' => false,
            'published_at' => null,
        ];
    }

    /**
     * Artikel yang sudah dipublikasikan.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => now()->subDays(fake()->numberBetween(0, 30)),
        ]);
    }

    /**
     * Artikel berstatus draf.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }
}
