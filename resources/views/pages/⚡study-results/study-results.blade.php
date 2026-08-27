<x-slot:breadcrumb>
    <div class="flex sm:hidden items-center gap-2 min-w-0">
        <flux:button :href="route('study', $chapter)" wire:navigate variant="ghost" size="sm" icon="chevron-left" class="label-sm shrink-0">
            Voltar
        </flux:button>
        <span class="font-mono text-label-sm text-primary truncate min-w-0">Resultados</span>
    </div>

    <flux:breadcrumbs class="hidden sm:flex">
        <flux:breadcrumbs.item :href="route('books')" class="breadcrumb-item">Livros</flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('books.show', $chapter->book_id)" class="breadcrumb-item">{{ $chapter->book->title }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('study', $chapter)" class="breadcrumb-item">{{ $this->results->chapterTitle }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item class="breadcrumb-item-current">Resultados</flux:breadcrumbs.item>
    </flux:breadcrumbs>
</x-slot:breadcrumb>

<main class="max-w-[1280px] mx-auto px-container-padding-mobile lg:px-container-padding-desktop py-8">
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="font-heading text-headline-xl text-on-background mb-2">Resultados da sessão</h1>
            <p class="text-subtitle">{{ $chapter->book->title }} — {{ $this->results->chapterTitle }}</p>
        </div>

        <div class="flex gap-4 bg-surface-container-low p-4 rounded-xl border border-outline-variant/50">
            <div class="flex flex-col">
                <span class="font-mono text-label-sm text-on-surface-variant uppercase tracking-wider">Proximidade média</span>
                <span class="font-heading text-headline-lg text-tertiary-fixed">{{ $this->results->averageScore }}%</span>
            </div>
            <div class="w-px bg-surface-variant"></div>
            <div class="flex flex-col">
                <span class="font-mono text-label-sm text-on-surface-variant uppercase tracking-wider">Perguntas</span>
                <span class="font-heading text-headline-lg text-on-surface">{{ $this->results->questionCount }}</span>
            </div>
        </div>
    </div>

    @if ($this->results->rows === [])
        <flux:callout icon="inbox">
            <flux:callout.heading class="font-heading">Nenhuma pergunta respondida ainda</flux:callout.heading>
            <flux:callout.text class="font-sans">Estude este capítulo para ver seus resultados aqui.</flux:callout.text>
        </flux:callout>
    @else
        <div class="flex flex-col gap-6 pb-12">
            @foreach ($this->results->rows as $row)
                @php
                    $tier = match (true) {
                        $row->skipped => 'skipped',
                        $row->score >= 70 => 'high',
                        $row->score >= 40 => 'medium',
                        default => 'low',
                    };

                    $badgeClasses = match ($tier) {
                        'high' => 'bg-tertiary-fixed/15 border-tertiary-fixed/30 text-tertiary-fixed',
                        'medium' => 'bg-secondary/15 border-secondary/30 text-secondary',
                        'low' => 'bg-error/15 border-error/30 text-error',
                        default => 'bg-surface-variant border-outline-variant text-on-surface-variant',
                    };
                @endphp

                <div class="bg-surface rounded-xl p-6 border border-outline-variant flex flex-col gap-6" wire:key="result-{{ $row->position }}">
                    <div class="flex gap-4">
                        <div class="w-8 h-8 rounded-full bg-surface-variant flex items-center justify-center shrink-0 mt-1">
                            <span class="font-mono text-label-md text-on-surface">Q{{ $row->position }}</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="heading-md mb-2">{{ $row->prompt }}</h3>

                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border {{ $badgeClasses }} mb-4">
                                @if ($tier === 'skipped')
                                    <flux:icon.forward class="size-4" />
                                    <span class="label-sm">Pulada</span>
                                @else
                                    <flux:icon.check-circle class="size-4" />
                                    <span class="label-sm">{{ $row->score }}% {{ $row->clozeSegments !== null ? 'de acertos' : 'de proximidade' }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @unless ($row->skipped)
                        @if ($row->clozeSegments !== null)
                        <div class="ml-0 md:ml-12">
                            <div class="panel-muted">
                                <div class="absolute top-0 left-0 w-1 h-full bg-secondary-container"></div>
                                <div class="flex items-center gap-2 mb-2">
                                    <flux:icon.pencil-square class="size-4 text-secondary-fixed" />
                                    <span class="font-mono text-label-md text-secondary-fixed">Frase</span>
                                </div>
                                <p class="font-sans text-body-sm leading-loose">@foreach ($row->clozeSegments as $seg)@if ($seg->blank)@if ($seg->correct)<span class="text-tertiary-fixed font-medium">{{ $seg->given }}</span>@else<span class="text-error/80 line-through decoration-1">{{ $seg->given ?: '—' }}</span> <span class="text-secondary">{{ $seg->expected }}</span>@endif@else{{ $seg->text }}@endif@endforeach</p>
                            </div>
                        </div>
                        @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 ml-0 md:ml-12">
                            <div class="panel-muted">
                                <div class="absolute top-0 left-0 w-1 h-full bg-secondary-container"></div>
                                <div class="flex items-center gap-2 mb-2">
                                    <flux:icon.shield-check class="size-4 text-secondary-fixed" />
                                    <span class="font-mono text-label-md text-secondary-fixed">Resposta padrão</span>
                                </div>
                                <p class="text-muted-sm">{{ $row->referenceAnswer }}</p>
                            </div>

                            <div class="panel-muted">
                                <div class="absolute top-0 left-0 w-1 h-full {{ match ($tier) { 'high' => 'bg-tertiary-fixed', 'medium' => 'bg-secondary', default => 'bg-error' } }}"></div>
                                <div class="flex items-center gap-2 mb-2">
                                    <flux:icon.user class="size-4 {{ match ($tier) { 'high' => 'text-tertiary-fixed', 'medium' => 'text-secondary', default => 'text-error' } }}" />
                                    <span class="font-mono text-label-md {{ match ($tier) { 'high' => 'text-tertiary-fixed', 'medium' => 'text-secondary', default => 'text-error' } }}">Sua resposta</span>
                                </div>
                                <p class="font-sans text-body-sm">
                                    @foreach ($row->answerSegments as $segment)
                                        <span class="{{ $segment->matched ? 'text-on-surface' : 'text-error/80 line-through decoration-1' }}">{{ $segment->text }}</span>{{ $loop->last ? '' : ' ' }}
                                    @endforeach
                                </p>
                            </div>
                        </div>
                        @endif
                    @endunless
                </div>
            @endforeach
        </div>
    @endif

    <div class="flex flex-wrap gap-3">
        <flux:button :href="route('study', $chapter)" wire:navigate variant="primary" icon="arrow-path" class="label-md">
            Estudar novamente
        </flux:button>
        <flux:button :href="route('books.show', $chapter->book_id)" wire:navigate variant="ghost" class="label-md">
            Voltar ao livro
        </flux:button>
    </div>
</main>
