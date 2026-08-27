<?php

namespace Tests\Feature;

use App\Actions\RefreshClozeBlanks;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefreshClozeBlanksTest extends TestCase
{
    use RefreshDatabase;

    private function action(): RefreshClozeBlanks
    {
        return app(RefreshClozeBlanks::class);
    }

    public function test_it_picks_blank_indices_when_a_question_becomes_cloze(): void
    {
        $question = Question::factory()->create([
            'reference_answer' => 'Deus fez uma aliança eterna com Abraão',
            'is_cloze' => true,
            'cloze_blank_indices' => null,
        ]);

        $this->action()->handle($question);

        $indices = $question->fresh()->cloze_blank_indices;
        $this->assertNotNull($indices);
        $this->assertNotEmpty($indices);
    }

    public function test_it_keeps_the_same_blanks_when_the_reference_answer_is_unchanged(): void
    {
        $question = Question::factory()->create([
            'reference_answer' => 'Deus fez uma aliança eterna com Abraão',
            'is_cloze' => true,
            'cloze_blank_indices' => [1],
        ]);

        // Simulate a later save that changed only the prompt.
        $question->update(['prompt' => 'Nova pergunta?']);
        $this->action()->handle($question);

        $this->assertSame([1], $question->fresh()->cloze_blank_indices);
    }

    public function test_it_recomputes_blanks_when_the_reference_answer_changes(): void
    {
        $question = Question::factory()->create([
            'reference_answer' => 'Deus fez uma aliança eterna com Abraão',
            'is_cloze' => true,
            'cloze_blank_indices' => [99],
        ]);

        $question->update(['reference_answer' => 'O rei Davi governou Israel com justiça']);
        $this->action()->handle($question);

        $indices = $question->fresh()->cloze_blank_indices;
        $this->assertNotContains(99, $indices);
        $this->assertNotEmpty($indices);
    }

    public function test_it_clears_the_blanks_when_cloze_is_turned_off(): void
    {
        $question = Question::factory()->create([
            'is_cloze' => false,
            'cloze_blank_indices' => [1, 2],
        ]);

        $this->action()->handle($question);

        $this->assertNull($question->fresh()->cloze_blank_indices);
    }
}
