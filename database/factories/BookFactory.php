<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\{AccessToken, Book};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'access_token_id' => AccessToken::factory(),
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'cover_path' => null,
        ];
    }
}
