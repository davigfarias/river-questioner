<?php

namespace Tests\Feature;

use App\Models\{AccessToken, Book, Chapter, Question};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StudyReviewPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviewing_a_chapter_from_another_token_is_not_found(): void
    {
        $token = AccessToken::factory()->create();
        $otherToken = AccessToken::factory()->create();
        $book = Book::factory()->for($otherToken)->create();
        $chapter = Chapter::factory()->for($book)->create();

        $this->withSession(['access_token_id' => $token->id])
            ->get(route('study.review', $chapter))
            ->assertNotFound();
    }

    public function test_it_shows_the_first_question_and_reference_answer(): void
    {
        [$token, $chapter, $question1] = $this->makeChapterWithTwoQuestions();

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::study-review', ['chapter' => $chapter])
            ->assertSet('index', 0)
            ->assertSee($question1->prompt)
            ->assertSee($question1->reference_answer);
    }

    public function test_next_advances_to_the_following_question_and_stops_at_the_end(): void
    {
        [$token, $chapter] = $this->makeChapterWithTwoQuestions();

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::study-review', ['chapter' => $chapter])
            ->call('next')
            ->assertSet('index', 1)
            ->call('next')
            ->assertSet('index', 1);
    }

    public function test_previous_goes_back_and_stops_at_the_start(): void
    {
        [$token, $chapter] = $this->makeChapterWithTwoQuestions();

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::study-review', ['chapter' => $chapter])
            ->call('next')
            ->call('previous')
            ->assertSet('index', 0)
            ->call('previous')
            ->assertSet('index', 0);
    }

    public function test_a_chapter_with_no_questions_shows_an_empty_state(): void
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::study-review', ['chapter' => $chapter])
            ->assertSee('Este capítulo ainda não tem perguntas');
    }

    /**
     * @return array{0: AccessToken, 1: Chapter, 2: Question}
     */
    private function makeChapterWithTwoQuestions(): array
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();
        $question1 = Question::factory()->for($chapter)->create([
            'prompt' => 'Pergunta 1',
            'reference_answer' => 'Resposta 1',
            'position' => 0,
        ]);
        Question::factory()->for($chapter)->create([
            'prompt' => 'Pergunta 2',
            'reference_answer' => 'Resposta 2',
            'position' => 1,
        ]);

        return [$token, $chapter, $question1];
    }
}
