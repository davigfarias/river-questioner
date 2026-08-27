<?php

namespace Tests\Feature;

use App\Actions\{SelectClozeBlanks, TokenizeAnswerText};
use Tests\TestCase;

class SelectClozeBlanksTest extends TestCase
{
    /**
     * @return array<int, array{word: bool, text: string, index: int|null}>
     */
    private function tokenize(string $text): array
    {
        return (new TokenizeAnswerText)->handle($text)->data;
    }

    public function test_it_never_selects_stopwords_short_words_or_numbers(): void
    {
        $tokens = $this->tokenize('O rei Davi tinha 30 anos quando começou a reinar em Israel');
        // eligible content words: rei, Davi, anos, quando(conj→excluded), começou, reinar, Israel
        // "quando" is a conjunction, "30" numeric, "O"/"a"/"em" stopwords/short.

        $indices = (new SelectClozeBlanks)->handle($tokens, 1.0)->data;

        $words = [];
        foreach ($tokens as $token) {
            if ($token['word']) {
                $words[$token['index']] = $token['text'];
            }
        }

        foreach ($indices as $index) {
            $this->assertNotContains(mb_strtolower($words[$index]), ['o', 'a', 'em', 'quando', 'tinha']);
            $this->assertNotSame('30', $words[$index]);
        }
    }

    public function test_the_blank_count_follows_the_ratio(): void
    {
        // 10 eligible content words.
        $tokens = $this->tokenize('alfa bravo charlie delta echo foxtrot golf hotel india juliet');

        $this->assertCount(3, (new SelectClozeBlanks)->handle($tokens, 0.30)->data);
        $this->assertCount(5, (new SelectClozeBlanks)->handle($tokens, 0.50)->data);
    }

    public function test_it_always_selects_at_least_one_blank_when_something_is_eligible(): void
    {
        $tokens = $this->tokenize('alfa bravo charlie');

        $this->assertCount(1, (new SelectClozeBlanks)->handle($tokens, 0.01)->data);
    }

    public function test_selected_indices_are_sorted_and_point_at_eligible_words(): void
    {
        $tokens = $this->tokenize('Deus fez uma aliança eterna com Abraão');

        $indices = (new SelectClozeBlanks)->handle($tokens, 1.0)->data;
        $sorted = $indices;
        sort($sorted);

        $this->assertSame($sorted, $indices);
        // eligible = Deus(0), fez(1), alianca(3), eterna(4), Abraao(6) — not uma(2)/com(5).
        $this->assertEqualsCanonicalizing([0, 1, 3, 4, 6], $indices);
    }

    public function test_an_answer_with_no_eligible_words_yields_no_blanks(): void
    {
        $tokens = $this->tokenize('e o a de');

        $this->assertSame([], (new SelectClozeBlanks)->handle($tokens)->data);
    }
}
