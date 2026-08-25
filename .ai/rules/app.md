---
paths:
  - 'app/**/*.php'
---

# App

## Group multi-class imports with {}; mini-actions do one thing
pint.json disables `single_import_per_statement` and enables `group_import`, so `use App\Models\{Book, Chapter};` is the required style for imports from the same namespace — always run `vendor/bin/pint --format agent` (no --dirty, this repo has no git) after editing PHP, it auto-groups them.

Architecture: `app/Actions/*.php` are mini-actions — `final readonly class` with a single `handle()` method and no other methods, each touching the DB for exactly one thing. `app/Actions/Orchestrators/*.php` compose several mini-actions via constructor-injected dependencies for multi-step flows. DTOs in `app/DTO` are `final readonly class`; any DTO stored as a public Livewire property (not just passed through an action) must implement `Livewire\Wireable` (see AttemptResultData/AnswerSegment) or hydration fails. Livewire "Form" objects in `app/Livewire/Forms` are the DTOs fed from the front end. Prefer constructor/method dependency injection over `app()`; `app()` is only acceptable inside a Livewire `#[Computed]` method, which can't receive injected params.
