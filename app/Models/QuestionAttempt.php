<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\QuestionAttemptFactory;
use Illuminate\Database\Eloquent\Attributes\{Fillable, Scope, UseFactory};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $question_id
 * @property int $access_token_id
 * @property string|null $answer_text
 * @property int|null $score
 * @property bool $skipped
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[UseFactory(QuestionAttemptFactory::class)]
#[Fillable(['question_id', 'access_token_id', 'answer_text', 'score', 'skipped'])]
class QuestionAttempt extends Model
{
    /** @use HasFactory<QuestionAttemptFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'skipped' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Question, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * @return BelongsTo<AccessToken, $this>
     */
    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(AccessToken::class);
    }

    /**
     * Scope a query to only include attempts made under the given access token.
     */
    #[Scope]
    protected function forAccessToken(Builder $query, AccessToken $accessToken): void
    {
        $query->where('access_token_id', $accessToken->id);
    }

    /**
     * Scope a query to only include attempts on the given questions.
     *
     * @param  iterable<int, int>  $questionIds
     */
    #[Scope]
    protected function forQuestions(Builder $query, iterable $questionIds): void
    {
        $query->whereIn('question_id', $questionIds);
    }
}
