---
paths:
  - 'resources/views/pages/**'
---

# Pages

## Livewire 4 page components: single root element required
Pages live in `resources/views/pages/⚡{name}/{name}.php` + `.blade.php` (Livewire 4 native pages, routed via `Route::livewire('/path', 'pages::name')`). The blade view's `<x-slot:breadcrumb>` doesn't count, but everything else must sit inside exactly ONE root element — if the page also renders a `<flux:modal>`, wrap the main content div and the modal(s) together in one outer `<div>`, or Livewire throws `MultipleRootElementsDetectedException` (only surfaces at render time, e.g. in tests, not from static review). Modal open/close from PHP uses `Flux::modal('name')->show()/->close()` (import `Flux\Flux`), not a bound boolean property.

There is no sidebar/navbar — `layouts/app/header.blade.php` is a slim top bar with the brand, a `flux:breadcrumbs` trail, and logout. Every authenticated page must define `<x-slot:breadcrumb>` (a `<flux:breadcrumbs>` with the page's ancestor trail, current page non-linked) at the top of its blade view; `layouts/app.blade.php` forwards it explicitly (`:breadcrumb="$breadcrumb ?? null"`) since Livewire only auto-forwards named page slots one level up, not further into nested layout components.
