<?php

namespace Tests\Feature;

use App\Models\{AccessToken, Book, Chapter, Question};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookShowPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_visiting_a_book_that_belongs_to_another_token_is_not_found(): void
    {
        $token = AccessToken::factory()->create();
        $otherToken = AccessToken::factory()->create();
        $book = Book::factory()->for($otherToken)->create();

        $this->withSession(['access_token_id' => $token->id])
            ->get(route('books.show', $book))
            ->assertNotFound();
    }

    public function test_it_creates_a_chapter(): void
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::book-show', ['book' => $book])
            ->set('chapterForm.title', 'Capítulo 1')
            ->call('saveChapter');

        $this->assertDatabaseHas('chapters', [
            'book_id' => $book->id,
            'title' => 'Capítulo 1',
            'position' => 0,
        ]);
    }

    public function test_it_creates_a_question_inside_a_chapter(): void
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::book-show', ['book' => $book])
            ->call('openCreateQuestion', $chapter->id)
            ->set('questionForm.prompt', 'O que é hesed?')
            ->set('questionForm.referenceAnswer', 'Um termo hebraico para amor leal.')
            ->call('saveQuestion');

        $this->assertDatabaseHas('questions', [
            'chapter_id' => $chapter->id,
            'prompt' => 'O que é hesed?',
            'reference_answer' => 'Um termo hebraico para amor leal.',
        ]);
    }

    public function test_it_updates_an_existing_question(): void
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();
        $question = Question::factory()->for($chapter)->create(['prompt' => 'Original']);

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::book-show', ['book' => $book])
            ->call('openEditQuestion', $question->id)
            ->set('questionForm.prompt', 'Atualizada')
            ->call('saveQuestion');

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'prompt' => 'Atualizada',
        ]);
    }

    public function test_toggling_cloze_on_persists_the_flag_and_picks_blank_indices(): void
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::book-show', ['book' => $book])
            ->call('openCreateQuestion', $chapter->id)
            ->set('questionForm.prompt', 'Qual a promessa a Abraão?')
            ->set('questionForm.referenceAnswer', 'Deus fez uma aliança eterna com Abraão')
            ->set('questionForm.isCloze', true)
            ->call('saveQuestion');

        $question = Question::where('chapter_id', $chapter->id)->firstOrFail();

        $this->assertTrue($question->is_cloze);
        $this->assertNotEmpty($question->cloze_blank_indices);
    }

    public function test_turning_cloze_off_clears_the_blank_indices(): void
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();
        $question = Question::factory()->for($chapter)->cloze()->create();

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::book-show', ['book' => $book])
            ->call('openEditQuestion', $question->id)
            ->assertSet('questionForm.isCloze', true)
            ->set('questionForm.isCloze', false)
            ->call('saveQuestion');

        $this->assertFalse($question->fresh()->is_cloze);
        $this->assertNull($question->fresh()->cloze_blank_indices);
    }

    public function test_editing_an_unrelated_field_keeps_the_cloze_blanks(): void
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();
        $question = Question::factory()->for($chapter)->cloze()->create();
        $original = $question->cloze_blank_indices;

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::book-show', ['book' => $book])
            ->call('openEditQuestion', $question->id)
            ->set('questionForm.prompt', 'Pergunta reescrita?')
            ->call('saveQuestion');

        $this->assertSame($original, $question->fresh()->cloze_blank_indices);
    }

    public function test_it_deletes_a_chapter_and_its_questions(): void
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();
        $question = Question::factory()->for($chapter)->create();

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::book-show', ['book' => $book])
            ->call('deleteChapter', $chapter->id);

        $this->assertDatabaseMissing('chapters', ['id' => $chapter->id]);
        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
    }

    public function test_it_reorders_questions_within_a_chapter(): void
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();
        $chapter = Chapter::factory()->for($book)->create();
        $first = Question::factory()->for($chapter)->create(['position' => 0]);
        $second = Question::factory()->for($chapter)->create(['position' => 1]);
        $third = Question::factory()->for($chapter)->create(['position' => 2]);

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::book-show', ['book' => $book])
            ->call('reorderQuestions', $third->id, 0, $chapter->id);

        $this->assertSame(0, $third->fresh()->position);
        $this->assertSame(1, $first->fresh()->position);
        $this->assertSame(2, $second->fresh()->position);
    }

    public function test_it_cannot_edit_a_question_belonging_to_another_book(): void
    {
        $token = AccessToken::factory()->create();
        $book = Book::factory()->for($token)->create();

        $otherBook = Book::factory()->for($token)->create();
        $otherChapter = Chapter::factory()->for($otherBook)->create();
        $otherQuestion = Question::factory()->for($otherChapter)->create();

        $this->withSession(['access_token_id' => $token->id]);

        $this->expectException(ModelNotFoundException::class);

        Livewire::test('pages::book-show', ['book' => $book])
            ->call('openEditQuestion', $otherQuestion->id);
    }
}
