<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\{AccessToken, Question, QuestionAttempt};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionAttempt>
 */
class QuestionAttemptFactory extends Factory
{
    protected $model = QuestionAttempt::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'access_token_id' => AccessToken::factory(),
            'answer_text' => fake()->sentence(),
            'score' => fake()->numberBetween(0, 100),
            'skipped' => false,
        ];
    }

    public function skipped(): static
    {
        return $this->state([
            'answer_text' => null,
            'score' => null,
            'skipped' => true,
        ]);
    }
}
