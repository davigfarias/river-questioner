<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\{AccessToken, Question, QuestionAttempt};
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class RecordQuestionAttempt
{
    public function handle(Question $question, AccessToken $accessToken, ?string $answerText, ?int $score, bool $skipped): Outcome
    {
        try {
            $attempt = QuestionAttempt::create([
                'question_id' => $question->id,
                'access_token_id' => $accessToken->id,
                'answer_text' => $answerText,
                'score' => $score,
                'skipped' => $skipped,
            ]);

            return Outcome::noViewMessage(data: $attempt);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível registrar a tentativa.');
        }
    }
}
