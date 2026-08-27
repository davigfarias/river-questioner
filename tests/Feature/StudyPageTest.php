<?php

namespace Tests\Feature;

use App\Models\{AccessToken, Book, Chapter, Question};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StudyPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_studying_a_chapter_from_another_token_is_not_found(): void
    {
        $token = AccessToken::factory()->create();
        $otherToken = AccessToken::factory()->create();
        $book = Book::factory()->for($otherToken)->create();
        $chapter = Chapter::factory()->for($book)->create();

        $this->withSession(['access_token_id' => $token->id])
            ->get(route('study', $chapter))
            ->assertNotFound();
    }

    public function test_submitting_an_exact_answer_scores_100_and_redirects_to_results(): void
    {
        [$token, $chapter, $question] = $this->makeChapterWithQuestion('O amor leal de Deus por seu povo.');

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::study', ['chapter' => $chapter])
            ->set('answer', 'O amor leal de Deus por seu povo.')
            ->call('submit')
            ->assertRedirect(route('study.results', $chapter));

        $this->assertDatabaseHas('question_attempts', [
            'question_id' => $question->id,
            'access_token_id' => $token->id,
            'skipped' => false,
            'score' => 100,
        ]);
    }

    public function test_submitting_a_completely_different_answer_scores_low(): void
    {
        [$token, $chapter, $question] = $this->makeChapterWithQuestion('O amor leal de Deus por seu povo.');

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::study', ['chapter' => $chapter])
            ->set('answer', 'batata frita gato azul')
            ->call('submit');

        $this->assertLessThan(30, $question->fresh()->attempts()->latest()->first()->score);
    }

    public function test_an_empty_answer_fails_validation(): void
    {
        [$token, $chapter] = $this->makeChapterWithQuestion('resposta');

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::study', ['chapter' => $chapter])
            ->set('answer', '')
            ->call('submit')
            ->assertHasErrors(['answer']);
    }

    public function test_skipping_records_a_skipped_attempt_and_advances(): void
    {
        [$token, , $question] = $this->makeChapterWithoutRedirect('resposta');

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::study', ['chapter' => $question->chapter])
            ->call('skip')
            ->assertSet('index', 1);

        $this->assertDatabaseHas('question_attempts', [
            'question_id' => $question->id,
            'access_token_id' => $token->id,
            'skipped' => true,
            'answer_text' => null,
            'score' => null,
        ]);
    }

    public function test_the_session_redirects_to_results_after_the_last_question(): void
    {
        [$token, $chapter] = $this->makeChapterWithQuestion('resposta');

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::study', ['chapter' => $chapter])
            ->call('skip')
            ->assertRedirect(route('study.results', $chapter));
    }

    public function test_a_cloze_question_renders_blank_inputs_and_hides_the_hint_aside(): void
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();
        Question::factory()->for($chapter)->cloze()->create();

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::study', ['chapter' => $chapter])
            ->assertSee('Complete as lacunas')
            ->assertSee('cloze-blank', false)
            ->assertDontSee('Ajuda de estudo');
    }

    public function test_submitting_cloze_blanks_grades_them_and_advances(): void
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();
        // reference "Deus fez uma aliança eterna com Abraão", blanks [3, 6].
        $question = Question::factory()->for($chapter)->cloze()->create();

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::study', ['chapter' => $chapter])
            ->set('clozeInputs.3', 'aliança')
            ->set('clozeInputs.6', 'Abraão')
            ->call('submit')
            ->assertRedirect(route('study.results', $chapter));

        $this->assertDatabaseHas('question_attempts', [
            'question_id' => $question->id,
            'access_token_id' => $token->id,
            'skipped' => false,
            'score' => 100,
        ]);
    }

    public function test_a_cloze_question_without_eligible_words_falls_back_to_free_text(): void
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();
        $question = Question::factory()->for($chapter)->create([
            'reference_answer' => 'e o a de',
            'is_cloze' => true,
            'cloze_blank_indices' => [],
        ]);

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::study', ['chapter' => $chapter])
            ->assertSee('Sua resposta')
            ->assertDontSee('Complete as lacunas')
            ->set('answer', 'e o a de')
            ->call('submit');

        $this->assertDatabaseHas('question_attempts', [
            'question_id' => $question->id,
            'skipped' => false,
        ]);
    }

    /**
     * @return array{0: AccessToken, 1: Chapter, 2: Question}
     */
    private function makeChapterWithQuestion(string $referenceAnswer): array
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();
        $question = Question::factory()->for($chapter)->create([
            'reference_answer' => $referenceAnswer,
        ]);

        return [$token, $chapter, $question];
    }

    /**
     * A chapter with two questions, so an action on the first question
     * doesn't trigger the end-of-session redirect.
     *
     * @return array{0: AccessToken, 1: Chapter, 2: Question}
     */
    private function makeChapterWithoutRedirect(string $referenceAnswer): array
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();
        $question = Question::factory()->for($chapter)->create([
            'reference_answer' => $referenceAnswer,
        ]);
        Question::factory()->for($chapter)->create();

        return [$token, $chapter, $question];
    }
}
