<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Attributes\{Fillable, Scope, UseFactory};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $chapter_id
 * @property string $prompt
 * @property string $reference_answer
 * @property string|null $keywords
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[UseFactory(QuestionFactory::class)]
#[Fillable(['chapter_id', 'prompt', 'reference_answer', 'keywords', 'position'])]
class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Chapter, $this>
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * @return HasMany<QuestionAttempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(QuestionAttempt::class);
    }

    /**
     * Scope a query to only include questions belonging to the given book.
     */
    #[Scope]
    protected function forBook(Builder $query, Book $book): void
    {
        $query->whereHas('chapter', fn (Builder $query) => $query->where('book_id', $book->id));
    }
}
