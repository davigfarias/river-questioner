<x-layouts::app.header :title="$title ?? null" :breadcrumb="$breadcrumb ?? null">
    {{ $slot }}
</x-layouts::app.header>
