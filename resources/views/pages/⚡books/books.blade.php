<x-slot:breadcrumb>
    <flux:breadcrumbs>
        <flux:breadcrumbs.item class="breadcrumb-item-current">Livros</flux:breadcrumbs.item>
    </flux:breadcrumbs>
</x-slot:breadcrumb>

<div>
<main class="p-container-padding-mobile md:p-container-padding-desktop max-w-[1280px] mx-auto">
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-gutter gap-4">
        <div>
            <h1 class="font-heading text-headline-xl text-on-surface mb-2">Meus Livros</h1>
            <p class="text-subtitle">Seus materiais de estudo e progresso.</p>
        </div>
        <div class="flex gap-4 w-full md:w-auto">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar livros..." class="w-full md:w-64 font-mono" />
        </div>
    </header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-gutter">
        @foreach ($this->books as $row)
            <a href="{{ route('books.show', $row->book) }}" wire:navigate wire:key="book-{{ $row->book->id }}" class="bg-surface-container rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow border border-outline-variant flex flex-col group relative">
                <div class="h-48 w-full relative overflow-hidden bg-surface-container-high">
                    @if ($row->book->coverUrl())
                        <img src="{{ $row->book->coverUrl() }}" alt="{{ $row->book->title }}" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity duration-300">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <flux:icon.book-open class="size-10 text-outline" />
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-surface-container to-transparent"></div>
                </div>

                <div class="p-6 flex-1 flex flex-col relative">
                    <div class="absolute -top-10 right-6 bg-surface rounded-full p-1 shadow-lg border border-outline-variant">
                        <x-progress-ring :percent="$row->progress->percent" :color="$row->progress->percent >= 70 ? 'tertiary' : 'primary'" />
                    </div>

                    <div class="mt-2">
                        @if ($row->book->author)
                            <span class="bg-secondary-container/20 text-secondary px-2 py-1 rounded-full font-mono text-label-sm mb-3 inline-block border border-secondary-container/40">{{ $row->book->author }}</span>
                        @endif
                        <h3 class="heading-md mb-1 line-clamp-2">{{ $row->book->title }}</h3>
                        <p class="text-muted-sm">{{ $row->progress->attemptedQuestions }}/{{ $row->progress->totalQuestions }} perguntas praticadas</p>
                    </div>

                    <div class="mt-auto pt-4 flex items-center justify-between">
                        <span class="font-mono text-label-sm text-on-surface-variant flex items-center gap-1">
                            <flux:icon.clock class="size-3.5" />
                            @if ($row->progress->lastStudiedAt)
                                {{ $row->progress->lastStudiedAt->diffForHumans() }}
                            @else
                                Ainda não estudado
                            @endif
                        </span>
                        <flux:icon.play-circle class="size-6 text-primary" />
                    </div>
                </div>
            </a>
        @endforeach

        <button type="button" wire:click="openCreateModal" class="bg-surface-container rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow border border-outline-variant border-dashed flex flex-col items-center justify-center p-8 group cursor-pointer hover:bg-surface-variant/20 min-h-[280px]">
            <div class="w-16 h-16 rounded-full bg-surface border border-outline-variant flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-sm">
                <flux:icon.plus class="size-7 text-primary" />
            </div>
            <h3 class="heading-md text-center mb-1">Novo livro</h3>
            <p class="text-muted-sm text-center">Cadastre um novo livro para estudar.</p>
        </button>
    </div>
</main>

<flux:modal name="create-book" class="modal-narrow">
    <form wire:submit="save" class="space-y-6">
        <flux:heading class="heading-md">Novo livro</flux:heading>

        <flux:input wire:model="form.title" label="Título" />

        <flux:input wire:model="form.author" label="Autor (opcional)" />

        <flux:input wire:model="form.cover" type="file" accept="image/*" label="Capa (opcional)" />

        <div class="modal-actions">
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">Criar</flux:button>
        </div>
    </form>
</flux:modal>
</div>
