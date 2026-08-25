<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\{Book, Chapter};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chapter>
 */
class ChapterFactory extends Factory
{
    protected $model = Chapter::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'title' => fake()->sentence(4),
            'position' => 0,
        ];
    }
}
