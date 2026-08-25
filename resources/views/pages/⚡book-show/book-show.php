<?php

use App\Actions\{CreateChapter, CreateQuestion, DeleteBook, DeleteBookCover, DeleteChapter, DeleteQuestion, StoreBookCover, UpdateBook, UpdateChapter, UpdateQuestion};
use App\Livewire\Forms\{BookForm, ChapterForm, QuestionForm};
use App\Models\{Book, Chapter, Question};
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\{Layout, Locked};
use Livewire\{Component, WithFileUploads};

new #[Layout('layouts::app')] class extends Component
{
    use WithFileUploads;

    #[Locked]
    public Book $book;

    public BookForm $bookForm;

    public ChapterForm $chapterForm;

    public QuestionForm $questionForm;

    public ?int $editingChapterId = null;

    public ?int $editingQuestionId = null;

    public ?int $activeChapterId = null;

    /** @var Collection<int, Chapter> */
    public Collection $chapters;

    public function mount(Book $book): void
    {
        abort_unless($book->access_token_id === (int) session('access_token_id'), 404);

        $this->book = $book;
        $this->bookForm->fillFromBook($book);
        $this->refreshChapters();
    }

    public function openEditBook(): void
    {
        $this->bookForm->fillFromBook($this->book);
        $this->resetValidation();

        Flux::modal('edit-book')->show();
    }

    public function saveBook(UpdateBook $updateBook, StoreBookCover $storeBookCover, DeleteBookCover $deleteBookCover): void
    {
        $this->bookForm->validate();

        $coverPath = $this->book->cover_path;

        if ($this->bookForm->cover !== null) {
            $deleteOutcome = $deleteBookCover->handle($this->book);

            if (! $deleteOutcome->success) {
                Flux::toast(text: $deleteOutcome->message, variant: 'danger');

                return;
            }

            $storeOutcome = $storeBookCover->handle($this->bookForm->cover);

            if (! $storeOutcome->success) {
                Flux::toast(text: $storeOutcome->message, variant: 'danger');

                return;
            }

            $coverPath = $storeOutcome->data;
        } elseif ($this->bookForm->removeCover) {
            $deleteOutcome = $deleteBookCover->handle($this->book);

            if (! $deleteOutcome->success) {
                Flux::toast(text: $deleteOutcome->message, variant: 'danger');

                return;
            }

            $coverPath = null;
        }

        $updateOutcome = $updateBook->handle($this->book, $this->bookForm->toData($coverPath));

        if (! $updateOutcome->success) {
            Flux::toast(text: $updateOutcome->message, variant: 'danger');

            return;
        }

        $this->book = $updateOutcome->data;

        Flux::modal('edit-book')->close();
        Flux::toast(text: 'Livro atualizado.', variant: 'success');
    }

    public function deleteBook(DeleteBook $deleteBook, DeleteBookCover $deleteBookCover): void
    {
        $coverOutcome = $deleteBookCover->handle($this->book);

        if (! $coverOutcome->success) {
            Flux::toast(text: $coverOutcome->message, variant: 'danger');

            return;
        }

        $deleteOutcome = $deleteBook->handle($this->book);

        if (! $deleteOutcome->success) {
            Flux::toast(text: $deleteOutcome->message, variant: 'danger');

            return;
        }

        $this->redirect(route('books'), navigate: true);
    }

    public function openCreateChapter(): void
    {
        $this->editingChapterId = null;
        $this->chapterForm->reset();
        $this->resetValidation();

        Flux::modal('chapter-form')->show();
    }

    public function openEditChapter(int $chapterId): void
    {
        $chapter = $this->findChapter($chapterId);

        $this->editingChapterId = $chapter->id;
        $this->chapterForm->fillFromChapter($chapter);
        $this->resetValidation();

        Flux::modal('chapter-form')->show();
    }

    public function saveChapter(CreateChapter $createChapter, UpdateChapter $updateChapter): void
    {
        $this->chapterForm->validate();

        $outcome = $this->editingChapterId === null
            ? $createChapter->handle($this->book, $this->chapterForm->toData())
            : $updateChapter->handle($this->findChapter($this->editingChapterId), $this->chapterForm->toData());

        if (! $outcome->success) {
            Flux::toast(text: $outcome->message, variant: 'danger');

            return;
        }

        $this->refreshChapters();

        Flux::modal('chapter-form')->close();
    }

    public function deleteChapter(int $chapterId, DeleteChapter $deleteChapter): void
    {
        $outcome = $deleteChapter->handle($this->findChapter($chapterId));

        if (! $outcome->success) {
            Flux::toast(text: $outcome->message, variant: 'danger');

            return;
        }

        $this->refreshChapters();
    }

    public function openCreateQuestion(int $chapterId): void
    {
        $this->activeChapterId = $chapterId;
        $this->editingQuestionId = null;
        $this->questionForm->reset();
        $this->resetValidation();

        Flux::modal('question-form')->show();
    }

    public function openEditQuestion(int $questionId): void
    {
        $question = $this->findQuestion($questionId);

        $this->activeChapterId = $question->chapter_id;
        $this->editingQuestionId = $question->id;
        $this->questionForm->fillFromQuestion($question);
        $this->resetValidation();

        Flux::modal('question-form')->show();
    }

    public function saveQuestion(CreateQuestion $createQuestion, UpdateQuestion $updateQuestion): void
    {
        $this->questionForm->validate();

        $outcome = $this->editingQuestionId === null
            ? $createQuestion->handle($this->findChapter($this->activeChapterId), $this->questionForm->toData())
            : $updateQuestion->handle($this->findQuestion($this->editingQuestionId), $this->questionForm->toData());

        if (! $outcome->success) {
            Flux::toast(text: $outcome->message, variant: 'danger');

            return;
        }

        $this->refreshChapters();

        Flux::modal('question-form')->close();
    }

    public function deleteQuestion(int $questionId, DeleteQuestion $deleteQuestion): void
    {
        $outcome = $deleteQuestion->handle($this->findQuestion($questionId));

        if (! $outcome->success) {
            Flux::toast(text: $outcome->message, variant: 'danger');

            return;
        }

        $this->refreshChapters();
    }

    private function findChapter(int $chapterId): Chapter
    {
        return $this->book->chapters()->findOrFail($chapterId);
    }

    private function findQuestion(int $questionId): Question
    {
        return Question::forBook($this->book)->findOrFail($questionId);
    }

    private function refreshChapters(): void
    {
        $this->chapters = $this->book->chapters()->with('questions')->get();
    }
};
