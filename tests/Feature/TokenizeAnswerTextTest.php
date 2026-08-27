<?php

namespace Tests\Feature;

use App\Actions\TokenizeAnswerText;
use Tests\TestCase;

class TokenizeAnswerTextTest extends TestCase
{
    public function test_it_splits_words_and_gaps_with_running_word_indices(): void
    {
        $tokens = (new TokenizeAnswerText)->handle('Deus fez uma aliança')->data;

        $words = array_values(array_filter($tokens, fn (array $token): bool => $token['word']));

        $this->assertSame(['Deus', 'fez', 'uma', 'aliança'], array_column($words, 'text'));
        $this->assertSame([0, 1, 2, 3], array_column($words, 'index'));
    }

    public function test_gap_tokens_have_a_null_index(): void
    {
        $tokens = (new TokenizeAnswerText)->handle('um dois')->data;

        $this->assertFalse($tokens[1]['word']);
        $this->assertNull($tokens[1]['index']);
        $this->assertSame(' ', $tokens[1]['text']);
    }

    public function test_it_preserves_punctuation_and_reconstructs_the_original_text(): void
    {
        $text = 'Deus fez uma aliança eterna com Abraão, o pai da fé.';

        $tokens = (new TokenizeAnswerText)->handle($text)->data;

        $this->assertSame($text, implode('', array_column($tokens, 'text')));
    }

    public function test_empty_text_yields_no_tokens(): void
    {
        $this->assertSame([], (new TokenizeAnswerText)->handle('')->data);
    }
}
