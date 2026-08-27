<?php

namespace Tests\Feature;

use App\Actions\{GradeClozeBlanks, TokenizeAnswerText};
use Tests\TestCase;

class GradeClozeBlanksTest extends TestCase
{
    /**
     * @return array<int, array{word: bool, text: string, index: int|null}>
     */
    private function tokenize(string $text): array
    {
        return (new TokenizeAnswerText)->handle($text)->data;
    }

    public function test_it_scores_all_correct_blanks_as_100(): void
    {
        $tokens = $this->tokenize('Deus fez uma aliança eterna com Abraão');

        $result = (new GradeClozeBlanks)->handle($tokens, [3, 6], [3 => 'aliança', 6 => 'Abraão'])->data;

        $this->assertSame(100, $result['score']);
        $this->assertTrue($result['blanks'][0]['correct']);
        $this->assertSame('aliança', $result['blanks'][0]['expected']);
    }

    public function test_matching_is_accent_and_case_insensitive(): void
    {
        $tokens = $this->tokenize('Deus fez uma aliança eterna com Abraão');

        $result = (new GradeClozeBlanks)->handle($tokens, [3, 6], [3 => 'ALIANCA', 6 => 'abraao'])->data;

        $this->assertSame(100, $result['score']);
    }

    public function test_a_wrong_or_missing_word_counts_as_incorrect(): void
    {
        $tokens = $this->tokenize('Deus fez uma aliança eterna com Abraão');

        $result = (new GradeClozeBlanks)->handle($tokens, [3, 6], [3 => 'promessa'])->data;

        $this->assertSame(0, $result['score']);
        $this->assertFalse($result['blanks'][0]['correct']);
        $this->assertSame('', $result['blanks'][1]['given']);
        $this->assertFalse($result['blanks'][1]['correct']);
    }

    public function test_partial_credit_is_the_fraction_of_correct_blanks(): void
    {
        $tokens = $this->tokenize('Deus fez uma aliança eterna com Abraão');

        $result = (new GradeClozeBlanks)->handle($tokens, [3, 4, 6], [3 => 'aliança', 4 => 'eterna', 6 => 'errado'])->data;

        $this->assertSame(67, $result['score']);
    }

    public function test_no_blanks_scores_100(): void
    {
        $result = (new GradeClozeBlanks)->handle([], [], [])->data;

        $this->assertSame(100, $result['score']);
        $this->assertSame([], $result['blanks']);
    }
}
