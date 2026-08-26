<?php

use App\Actions\FindQuestionsForChapter;
use App\Models\{Chapter, Question};
use Flux\Flux;
use Livewire\Attributes\{Computed, Layout, Locked, Title};
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Revisão')] class extends Component
{
    #[Locked]
    public int $chapterId;

    #[Locked]
    public int $bookId;

    public string $bookTitle = '';

    public string $chapterTitle = '';

    /** @var array<int, int> */
    #[Locked]
    public array $questionIds = [];

    #[Locked]
    public int $totalQuestions = 0;

    public int $index = 0;

    public function mount(FindQuestionsForChapter $findQuestionsForChapter, Chapter $chapter): void
    {
        abort_unless($chapter->book->access_token_id === (int) session('access_token_id'), 404);

        $outcome = $findQuestionsForChapter->handle($chapter);

        if (! $outcome->success) {
            Flux::toast(text: $outcome->message, variant: 'danger');
            $this->redirect(route('books.show', $chapter->book_id), navigate: true);

            return;
        }

        $this->chapterId = $chapter->id;
        $this->bookId = $chapter->book_id;
        $this->bookTitle = $chapter->book->title;
        $this->chapterTitle = $chapter->title;
        $this->questionIds = $outcome->data->pluck('id')->all();
        $this->totalQuestions = count($this->questionIds);
    }

    #[Computed]
    public function question(): ?Question
    {
        $id = $this->questionIds[$this->index] ?? null;

        return $id ? Question::find($id) : null;
    }

    #[Computed]
    public function progress(): int
    {
        return $this->totalQuestions <= 1
            ? 100
            : (int) round(($this->index / ($this->totalQuestions - 1)) * 100);
    }

    public function previous(): void
    {
        if ($this->index > 0) {
            $this->index--;
            unset($this->question);
        }
    }

    public function next(): void
    {
        if ($this->index < $this->totalQuestions - 1) {
            $this->index++;
            unset($this->question);
        }
    }
};
