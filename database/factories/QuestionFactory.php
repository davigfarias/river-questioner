<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\{Chapter, Question};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chapter_id' => Chapter::factory(),
            'prompt' => fake()->sentence().'?',
            'reference_answer' => fake()->paragraph(),
            'keywords' => implode(', ', fake()->words(3)),
            'position' => 0,
        ];
    }
}
