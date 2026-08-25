<?php

use App\Actions\{CreateBook, StoreBookCover};
use App\Actions\Orchestrators\BuildLibrarySummaryOrchestrator;
use App\DTO\LibraryBookRow;
use App\Livewire\Forms\BookForm;
use App\Models\AccessToken;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\{Computed, Layout, Title};
use Livewire\{Component, WithFileUploads};

new #[Layout('layouts::app')] #[Title('Livros')] class extends Component
{
    use WithFileUploads;

    public BookForm $form;

    public string $search = '';

    /**
     * @return Collection<int, LibraryBookRow>
     */
    #[Computed]
    public function books(): Collection
    {
        $outcome = app(BuildLibrarySummaryOrchestrator::class)->handle(
            AccessToken::findOrFail(session('access_token_id')),
            $this->search,
        );

        if (! $outcome->success) {
            Flux::toast(text: $outcome->message, variant: 'danger');

            return collect();
        }

        return $outcome->data;
    }

    public function openCreateModal(): void
    {
        $this->form->reset();
        $this->resetValidation();

        Flux::modal('create-book')->show();
    }

    public function save(CreateBook $createBook, StoreBookCover $storeBookCover): void
    {
        $this->form->validate();

        $accessToken = AccessToken::findOrFail(session('access_token_id'));

        $coverPath = null;

        if ($this->form->cover !== null) {
            $coverOutcome = $storeBookCover->handle($this->form->cover);

            if (! $coverOutcome->success) {
                Flux::toast(text: $coverOutcome->message, variant: 'danger');

                return;
            }

            $coverPath = $coverOutcome->data;
        }

        $bookOutcome = $createBook->handle($accessToken, $this->form->toData($coverPath));

        if (! $bookOutcome->success) {
            Flux::toast(text: $bookOutcome->message, variant: 'danger');

            return;
        }

        $book = $bookOutcome->data;

        $this->form->reset();
        unset($this->books);

        Flux::modal('create-book')->close();
        Flux::toast(heading: 'Livro criado', text: "\"{$book->title}\" está pronto para receber capítulos.", variant: 'success');
    }
};
