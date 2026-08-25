<?php

namespace Tests\Feature;

use App\Models\{AccessToken, Book, Chapter, Question, QuestionAttempt};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StudyResultsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewing_results_from_another_token_is_not_found(): void
    {
        $token = AccessToken::factory()->create();
        $otherToken = AccessToken::factory()->create();
        $book = Book::factory()->for($otherToken)->create();
        $chapter = Chapter::factory()->for($book)->create();

        $this->withSession(['access_token_id' => $token->id])
            ->get(route('study.results', $chapter))
            ->assertNotFound();
    }

    public function test_it_shows_the_latest_attempt_per_question_and_the_average_score(): void
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();

        $question1 = Question::factory()->for($chapter)->create([
            'prompt' => 'Pergunta 1',
            'reference_answer' => 'resposta certa',
        ]);
        $question2 = Question::factory()->for($chapter)->create([
            'prompt' => 'Pergunta 2',
            'reference_answer' => 'outra resposta',
        ]);

        // An older, worse attempt on question 1 — should be superseded by the latest one.
        QuestionAttempt::factory()->for($question1)->for($token)->create(['score' => 10]);
        QuestionAttempt::factory()->for($question1)->for($token)->create(['score' => 90]);
        QuestionAttempt::factory()->for($question2)->for($token)->skipped()->create();

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::study-results', ['chapter' => $chapter])
            ->assertSee('90')
            ->assertSee('Pergunta 1')
            ->assertSee('Pergunta 2')
            ->assertSee('Pulada');
    }

    public function test_a_chapter_with_no_attempts_shows_an_empty_state(): void
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();
        Question::factory()->for($chapter)->create();

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::study-results', ['chapter' => $chapter])
            ->assertSee('Nenhuma pergunta respondida ainda');
    }
}
