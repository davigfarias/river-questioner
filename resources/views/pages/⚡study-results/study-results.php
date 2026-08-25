<?php

use App\Actions\Orchestrators\BuildChapterResultsOrchestrator;
use App\DTO\ChapterResultsData;
use App\Models\{AccessToken, Chapter};
use Flux\Flux;
use Livewire\Attributes\{Computed, Layout, Locked, Title};
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Resultados')] class extends Component
{
    #[Locked]
    public Chapter $chapter;

    public function mount(Chapter $chapter): void
    {
        abort_unless($chapter->book->access_token_id === (int) session('access_token_id'), 404);

        $this->chapter = $chapter;
    }

    #[Computed]
    public function results(): ChapterResultsData
    {
        $outcome = app(BuildChapterResultsOrchestrator::class)->handle(
            $this->chapter,
            AccessToken::findOrFail(session('access_token_id')),
        );

        if (! $outcome->success) {
            Flux::toast(text: $outcome->message, variant: 'danger');

            return new ChapterResultsData(
                chapterTitle: $this->chapter->title,
                averageScore: 0,
                questionCount: 0,
                rows: [],
            );
        }

        return $outcome->data;
    }
};
