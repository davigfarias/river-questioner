<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class QuestionResultRow
{
    /**
     * @param  array<int, AnswerSegment>  $answerSegments
     */
    public function __construct(
        public int $position,
        public string $prompt,
        public string $referenceAnswer,
        public ?string $answerText,
        public ?int $score,
        public bool $skipped,
        public array $answerSegments,
    ) {}
}
