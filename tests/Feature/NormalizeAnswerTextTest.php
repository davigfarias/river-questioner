<?php

namespace Tests\Feature;

use App\Actions\NormalizeAnswerText;
use Tests\TestCase;

class NormalizeAnswerTextTest extends TestCase
{
    public function test_it_folds_latin_accents(): void
    {
        $action = new NormalizeAnswerText;

        $this->assertSame($action->handle('está em relação')->data, $action->handle('esta em relacao')->data);
    }

    public function test_it_lowercases_and_strips_punctuation(): void
    {
        $action = new NormalizeAnswerText;

        $this->assertSame(['ola', 'mundo'], $action->handle('Olá, Mundo!')->data);
    }

    public function test_it_collapses_whitespace(): void
    {
        $action = new NormalizeAnswerText;

        $this->assertSame(['um', 'dois'], $action->handle("  um   dois\n")->data);
    }

    public function test_empty_text_yields_no_tokens(): void
    {
        $action = new NormalizeAnswerText;

        $this->assertSame([], $action->handle('   ')->data);
    }
}
