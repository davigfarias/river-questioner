<?php

use App\Actions\Orchestrators\{StartStudySessionOrchestrator, SubmitAnswerOrchestrator, SubmitClozeAnswerOrchestrator};
use App\Actions\{RecordQuestionAttempt, TokenizeAnswerText};
use App\Models\{AccessToken, Chapter, Question};
use Flux\Flux;
use Livewire\Attributes\{Computed, Layout, Locked, Title};
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Estudar')] class extends Component
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

    public string $answer = '';

    /** @var array<int, string> word index => filled value */
    public array $clozeInputs = [];

    public int $hintLevel = 0;

    public int $sessionRun = 0;

    public function mount(StartStudySessionOrchestrator $orchestrator, Chapter $chapter): void
    {
        abort_unless($chapter->book->access_token_id === (int) session('access_token_id'), 404);

        $this->startSession($orchestrator, $chapter);
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
        return $this->totalQuestions === 0
            ? 100
            : (int) round(($this->index / $this->totalQuestions) * 100);
    }

    #[Computed]
    public function isClozeQuestion(): bool
    {
        return $this->question !== null
            && $this->question->is_cloze
            && ! empty($this->question->cloze_blank_indices);
    }

    /**
     * The reference answer split for rendering: blank segments carry the word
     * index to bind an input to; text segments carry the visible text. Expected
     * words for blanks are never included, so they never reach the client.
     *
     * @return array<int, array{blank: bool, index?: int, text?: string}>
     */
    #[Computed]
    public function clozeSegments(): array
    {
        if (! $this->isClozeQuestion()) {
            return [];
        }

        $blankIndices = array_flip($this->question->cloze_blank_indices);
        $tokens = app(TokenizeAnswerText::class)->handle($this->question->reference_answer)->data ?? [];

        $segments = [];

        foreach ($tokens as $token) {
            if ($token['word'] && isset($blankIndices[$token['index']])) {
                $segments[] = ['blank' => true, 'index' => $token['index']];

                continue;
            }

            $segments[] = ['blank' => false, 'text' => $token['text']];
        }

        return $segments;
    }

    /**
     * @return array<int, string>|null null means no hint was requested yet
     */
    public function hintPreview(): ?array
    {
        if ($this->hintLevel === 0 || $this->question === null) {
            return null;
        }

        $keywords = $this->keywordList();

        if ($keywords === []) {
            return [];
        }

        $count = (int) ceil(count($keywords) * min($this->hintLevel, 3) / 3);

        return array_slice($keywords, 0, $count);
    }

    /**
     * @return array<int, string>
     */
    private function keywordList(): array
    {
        if ($this->question === null || blank($this->question->keywords)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $this->question->keywords))));
    }

    public function revealHint(): void
    {
        if ($this->hintLevel < 3) {
            $this->hintLevel++;
        }
    }

    public function submit(SubmitAnswerOrchestrator $orchestrator, SubmitClozeAnswerOrchestrator $clozeOrchestrator): void
    {
        $question = $this->question;

        if ($question === null) {
            return;
        }

        $accessToken = AccessToken::findOrFail(session('access_token_id'));

        if ($this->isClozeQuestion()) {
            $outcome = $clozeOrchestrator->handle($question, $accessToken, $this->clozeInputs);
        } else {
            $this->validate(['answer' => ['required', 'string']]);

            $outcome = $orchestrator->handle($question, $accessToken, $this->answer);
        }

        if (! $outcome->success) {
            Flux::toast(text: $outcome->message, variant: 'danger');

            return;
        }

        $this->advance();
    }

    public function skip(RecordQuestionAttempt $recordQuestionAttempt): void
    {
        $question = $this->question;

        if ($question === null) {
            return;
        }

        $accessToken = AccessToken::findOrFail(session('access_token_id'));

        $outcome = $recordQuestionAttempt->handle($question, $accessToken, answerText: null, score: null, skipped: true);

        if (! $outcome->success) {
            Flux::toast(text: $outcome->message, variant: 'danger');

            return;
        }

        $this->advance();
    }

    public function restart(StartStudySessionOrchestrator $orchestrator): void
    {
        $this->startSession($orchestrator, Chapter::findOrFail($this->chapterId));
    }

    private function startSession(StartStudySessionOrchestrator $orchestrator, Chapter $chapter): void
    {
        $outcome = $orchestrator->handle($chapter);

        if (! $outcome->success) {
            Flux::toast(text: $outcome->message, variant: 'danger');
            $this->redirect(route('books.show', $chapter->book_id), navigate: true);

            return;
        }

        $session = $outcome->data;

        $this->chapterId = $chapter->id;
        $this->bookId = $chapter->book_id;
        $this->bookTitle = $session->bookTitle;
        $this->chapterTitle = $session->chapterTitle;
        $this->questionIds = $session->questionIds;
        $this->totalQuestions = count($session->questionIds);
        $this->index = 0;
        $this->answer = '';
        $this->clozeInputs = [];
        $this->hintLevel = 0;
        $this->sessionRun++;
        unset($this->question, $this->isClozeQuestion, $this->clozeSegments);
    }

    private function advance(): void
    {
        $this->index++;

        if ($this->index >= $this->totalQuestions) {
            $this->redirect(route('study.results', ['chapter' => $this->chapterId]), navigate: true);

            return;
        }

        $this->answer = '';
        $this->clozeInputs = [];
        $this->hintLevel = 0;
        unset($this->question, $this->isClozeQuestion, $this->clozeSegments);
    }
};
