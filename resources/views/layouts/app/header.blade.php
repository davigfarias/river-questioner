<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-background text-on-background font-sans flex flex-col">
        <header class="sticky top-0 z-40 bg-surface-container-low border-b border-outline-variant">
            <div class="max-w-7xl mx-auto px-container-padding-mobile md:px-container-padding-desktop py-3 flex items-center gap-3">
                <a href="{{ route('books') }}" wire:navigate class="flex items-center gap-2 shrink-0">
                   <flux:brand  logo="/img/river.png" name="Questioner" />
                </a>

                @isset($breadcrumb)
                    <flux:separator vertical class="h-10 shrink-0 hidden sm:block" />

                    <div class="min-w-0 flex-1 overflow-x-auto">
                        {{ $breadcrumb }}
                    </div>
                @endisset

            </div>
        </header>

        <div class="flex-1">
            {{ $slot }}
        </div>

        <footer class="hidden md:block shrink-0">
                <div class="mx-auto flex items-center justify-center gap-2 px-6 py-4 text-sm text-on-surface-variant">

                    <span>Desenvolvido orgulhosamente em</span>

                    <span class="flex items-center gap-1 font-semibold text-red-500">
                        <img
                            src="https://upload.wikimedia.org/wikipedia/commons/9/9a/Laravel.svg"
                            alt="Laravel"
                            class="h-4 w-4"
                        >
                        Laravel
                    </span>

                    <span>por</span>

                    <a
                        href="https://github.com/davigfarias"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center gap-1 font-semibold hover:opacity-80"
                    >
                        <img
                            src="https://uxwing.com/wp-content/themes/uxwing/download/brands-and-social-media/github-white-icon.png"
                            alt="GitHub"
                            class="h-4 w-4"
                        >
                        Dave Farias
                    </a>

                </div>
            </footer>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>

</html>
