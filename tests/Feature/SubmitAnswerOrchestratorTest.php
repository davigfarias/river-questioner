<?php

namespace Tests\Feature;

use App\Actions\Orchestrators\SubmitAnswerOrchestrator;
use App\Models\{AccessToken, Book, Chapter, Question};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmitAnswerOrchestratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_scores_the_answer_and_records_an_attempt(): void
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();
        $question = Question::factory()->for($chapter)->create([
            'reference_answer' => 'O amor leal de Deus.',
        ]);

        $outcome = app(SubmitAnswerOrchestrator::class)->handle($question, $token, 'O amor leal de Deus.');
        $result = $outcome->data;

        $this->assertTrue($outcome->success);
        $this->assertSame(100, $result->score);
        $this->assertNotEmpty($result->referenceSegments);
        $this->assertNotEmpty($result->answerSegments);

        $this->assertDatabaseHas('question_attempts', [
            'question_id' => $question->id,
            'access_token_id' => $token->id,
            'answer_text' => 'O amor leal de Deus.',
            'score' => 100,
            'skipped' => false,
        ]);
    }
}
