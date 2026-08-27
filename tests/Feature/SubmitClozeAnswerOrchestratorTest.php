<?php

namespace Tests\Feature;

use App\Actions\Orchestrators\SubmitClozeAnswerOrchestrator;
use App\Models\{AccessToken, Book, Chapter, Question};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmitClozeAnswerOrchestratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_grades_the_blanks_and_records_a_cloze_attempt(): void
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();
        // reference "Deus fez uma aliança eterna com Abraão", blanks [3, 6].
        $question = Question::factory()->for($chapter)->cloze()->create();

        $outcome = app(SubmitClozeAnswerOrchestrator::class)->handle($question, $token, [
            3 => 'aliança',
            6 => 'errado',
        ]);

        $this->assertTrue($outcome->success);

        $attempt = $question->attempts()->latest('id')->first();

        $this->assertSame(50, $attempt->score);
        $this->assertFalse($attempt->skipped);
        $this->assertSame('aliança, errado', $attempt->answer_text);
        $this->assertCount(2, $attempt->cloze_blanks);
        $this->assertTrue($attempt->cloze_blanks[0]['correct']);
        $this->assertFalse($attempt->cloze_blanks[1]['correct']);
        $this->assertSame('Abraão', $attempt->cloze_blanks[1]['expected']);
    }
}
