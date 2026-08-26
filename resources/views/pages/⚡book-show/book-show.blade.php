<x-slot:breadcrumb>
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('books')" class="breadcrumb-item">Livros</flux:breadcrumbs.item>
        <flux:breadcrumbs.item class="breadcrumb-item-current">{{ $book->title }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>
</x-slot:breadcrumb>

<div>
<main class="p-container-padding-mobile md:p-container-padding-desktop max-w-3xl mx-auto space-y-8">
    <div class="flex items-start gap-4">
        <div class="w-20 h-28 shrink-0 rounded-lg overflow-hidden bg-surface-container-high flex items-center justify-center border border-outline-variant">
            @if ($book->coverUrl())
                <img src="{{ $book->coverUrl() }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
            @else
                <flux:icon.book-open class="size-8 text-outline" />
            @endif
        </div>

        <div class="flex-1 min-w-0">
            <flux:heading class="font-heading text-headline-xl text-on-surface truncate">{{ $book->title }}</flux:heading>
            @if ($book->author)
                <flux:text class="font-sans text-body-md text-on-surface-variant">{{ $book->author }}</flux:text>
            @endif

            <div class="flex gap-2 mt-3">
                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openEditBook" class="label-sm">Editar</flux:button>
                <flux:modal.trigger name="delete-book">
                    <flux:button size="sm" variant="ghost" icon="trash" class="label-sm">Excluir</flux:button>
                </flux:modal.trigger>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <flux:heading class="font-heading text-headline-lg text-on-surface">Capítulos</flux:heading>
        <flux:button size="sm" variant="primary" icon="plus" wire:click="openCreateChapter" class="label-sm">Novo capítulo</flux:button>
    </div>

    @if ($chapters->isEmpty())
        <flux:callout icon="list-bullet">
            <flux:callout.heading class="font-heading">Nenhum capítulo ainda</flux:callout.heading>
            <flux:callout.text class="font-sans">Crie um capítulo para começar a adicionar perguntas.</flux:callout.text>
        </flux:callout>
    @else
        <div class="space-y-3">
            @foreach ($chapters as $chapter)
                <details class="rounded-xl border border-outline-variant overflow-hidden bg-surface-container" wire:key="chapter-{{ $chapter->id }}" @if ($loop->first) open @endif>
                    <summary class="flex items-center justify-between gap-3 p-4 cursor-pointer bg-surface-container-low select-none">
                        <div class="min-w-0">
                            <flux:heading class="heading-md truncate">{{ $chapter->title }}</flux:heading>
                            <flux:text class="font-mono text-label-sm text-on-surface-variant">{{ $chapter->questions->count() }} {{ $chapter->questions->count() === 1 ? 'pergunta' : 'perguntas' }}</flux:text>
                        </div>

                        <div class="flex items-center gap-1 shrink-0">
                            <flux:button size="sm" variant="primary" icon="play" :disabled="$chapter->questions->isEmpty()" :href="route('study', $chapter)" wire:navigate class="label-sm">
                                Estudar
                            </flux:button>
                            <flux:button size="sm" variant="ghost" icon="book-open" :disabled="$chapter->questions->isEmpty()" :href="route('study.review', $chapter)" wire:navigate class="label-sm">
                                Revisão
                            </flux:button>
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openEditChapter({{ $chapter->id }})" />
                            <flux:modal.trigger name="delete-chapter-{{ $chapter->id }}">
                                <flux:button size="sm" variant="ghost" icon="trash" />
                            </flux:modal.trigger>
                        </div>
                    </summary>

                    <div class="p-4 pt-0 space-y-2">
                        @forelse ($chapter->questions as $question)
                            <div class="flex items-start justify-between gap-3 p-3 rounded-lg bg-surface border border-outline-variant" wire:key="question-{{ $question->id }}">
                                <flux:text class="font-sans text-body-sm min-w-0 truncate">{{ $question->prompt }}</flux:text>

                                <div class="flex items-center gap-1 shrink-0">
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openEditQuestion({{ $question->id }})" />
                                    <flux:modal.trigger name="delete-question-{{ $question->id }}">
                                        <flux:button size="sm" variant="ghost" icon="trash" />
                                    </flux:modal.trigger>
                                </div>
                            </div>

                            <flux:modal name="delete-question-{{ $question->id }}" class="modal-narrow" wire:key="delete-question-modal-{{ $question->id }}">
                                <div class="space-y-6">
                                    <div>
                                        <flux:heading class="heading-md">Excluir pergunta?</flux:heading>
                                        <flux:subheading class="text-muted-sm">Esta ação não pode ser desfeita.</flux:subheading>
                                    </div>

                                    <div class="modal-actions">
                                        <flux:modal.close>
                                            <flux:button variant="ghost">Cancelar</flux:button>
                                        </flux:modal.close>
                                        <flux:button variant="danger" wire:click="deleteQuestion({{ $question->id }})">Excluir</flux:button>
                                    </div>
                                </div>
                            </flux:modal>
                        @empty
                            <flux:text class="text-muted-sm">Nenhuma pergunta neste capítulo ainda.</flux:text>
                        @endforelse

                        <flux:button size="sm" variant="ghost" icon="plus" wire:click="openCreateQuestion({{ $chapter->id }})" class="mt-2 label-sm">
                            Adicionar pergunta
                        </flux:button>
                    </div>
                </details>

                <flux:modal name="delete-chapter-{{ $chapter->id }}" class="modal-narrow" wire:key="delete-chapter-modal-{{ $chapter->id }}">
                    <div class="space-y-6">
                        <div>
                            <flux:heading class="heading-md">Excluir capítulo?</flux:heading>
                            <flux:subheading class="text-muted-sm">Excluir "{{ $chapter->title }}" e todas as suas perguntas? Esta ação não pode ser desfeita.</flux:subheading>
                        </div>

                        <div class="modal-actions">
                            <flux:modal.close>
                                <flux:button variant="ghost">Cancelar</flux:button>
                            </flux:modal.close>
                            <flux:button variant="danger" wire:click="deleteChapter({{ $chapter->id }})">Excluir</flux:button>
                        </div>
                    </div>
                </flux:modal>
            @endforeach
        </div>
    @endif
</main>

<flux:modal name="delete-book" class="modal-narrow">
    <div class="space-y-6">
        <div>
            <flux:heading class="heading-md">Excluir livro?</flux:heading>
            <flux:subheading class="text-muted-sm">Excluir "{{ $book->title }}" e todos os seus capítulos e perguntas? Esta ação não pode ser desfeita.</flux:subheading>
        </div>

        <div class="modal-actions">
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" wire:click="deleteBook">Excluir</flux:button>
        </div>
    </div>
</flux:modal>

<flux:modal name="edit-book" class="modal-narrow">
    <form wire:submit="saveBook" class="space-y-6">
        <flux:heading class="heading-md">Editar livro</flux:heading>

        <flux:input wire:model="bookForm.title" label="Título" />

        <flux:input wire:model="bookForm.author" label="Autor (opcional)" />

        <flux:input wire:model="bookForm.cover" type="file" accept="image/*" label="Nova capa (opcional)" />

        @if ($book->coverUrl())
            <flux:checkbox wire:model="bookForm.removeCover" label="Remover capa atual" />
        @endif

        <div class="modal-actions">
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">Salvar</flux:button>
        </div>
    </form>
</flux:modal>

<flux:modal name="chapter-form" class="modal-narrow">
    <form wire:submit="saveChapter" class="space-y-6">
        <flux:heading class="heading-md">{{ $editingChapterId === null ? 'Novo capítulo' : 'Editar capítulo' }}</flux:heading>

        <flux:input wire:model="chapterForm.title" label="Título" />

        <div class="modal-actions">
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">Salvar</flux:button>
        </div>
    </form>
</flux:modal>

<flux:modal name="question-form" class="w-full md:w-[32rem]">
    <form wire:submit="saveQuestion" class="space-y-6">
        <flux:heading class="heading-md">{{ $editingQuestionId === null ? 'Nova pergunta' : 'Editar pergunta' }}</flux:heading>

        <flux:textarea wire:model="questionForm.prompt" label="Pergunta" rows="3" />

        <flux:textarea wire:model="questionForm.referenceAnswer" label="Resposta padrão" rows="5" />

        <flux:input wire:model="questionForm.keywords" label="Palavras-chave (separadas por vírgula)" placeholder="aliança, fidelidade, pacto" description="Usadas na dica progressiva durante o estudo, em vez de revelar a resposta." />

        <div class="modal-actions">
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">Salvar</flux:button>
        </div>
    </form>
</flux:modal>
</div>
