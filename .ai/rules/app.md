---
paths:
  - 'app/**/*.php'
---

# App

## Group multi-class imports with {}; mini-actions do one thing
pint.json disables `single_import_per_statement` and enables `group_import`, so `use App\Models\{Book, Chapter};` is the required style for imports from the same namespace — always run `vendor/bin/pint --format agent` (no --dirty, this repo has no git) after editing PHP, it auto-groups them.

Architecture: `app/Actions/*.php` are mini-actions — `final readonly class` with a single `handle()` method and no other methods, each touching the DB for exactly one thing. `app/Actions/Orchestrators/*.php` compose several mini-actions via constructor-injected dependencies for multi-step flows. DTOs in `app/DTO` are `final readonly class`; any DTO stored as a public Livewire property (not just passed through an action) must implement `Livewire\Wireable` (see AttemptResultData/AnswerSegment) or hydration fails. Livewire "Form" objects in `app/Livewire/Forms` are the DTOs fed from the front end. Prefer constructor/method dependency injection over `app()`; `app()` is only acceptable inside a Livewire `#[Computed]` method, which can't receive injected params.

## Tuning values go in config/ + #[Config] injection, not class constants
Numeric/tunable business values (ratios, retry counts, thresholds, throttle limits) live in dedicated config files (config/cloze.php, config/access_tokens.php), never as class constants. Inject with Illuminate\Container\Attributes\Config on a constructor-promoted property: `#[Config('cloze.blank_ratio')] private float $blankRatio = 0.30`. Keep a default equal to the config value so `new Action` still works in tests; the container applies the attribute when it builds the class. Mini-actions may carry a constructor for this even though the usual rule is handle()-only. #[Config] cannot annotate a call-site argument (e.g. RateLimiter::hit($key, #[Config] $x) is invalid) — for Livewire pages put it on the action-method parameter (joinSession(..., #[Config('...')] int $x)); Livewire resolves those through the container. Data structures (e.g. PortugueseStopwords::LIST) stay as constants.
