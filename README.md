# River Questioner

A free-text Q&A study app — organize books into chapters and questions, then study them by typing your own answer from memory instead of picking a multiple-choice option. Built with Laravel and Livewire. Part of the "river" series of apps, sharing a single lightweight access-token auth system across the family of apps.

## Features

- **Books, chapters & questions** — organize study material into books, each with ordered chapters, each with ordered questions and a reference answer.
- **Free-text scoring** — no multiple choice. Type your answer and it's word-diffed against the reference answer (accent-folded, case-insensitive) to produce a proximity score, with matched/unmatched segments highlighted on the results page.
- **Progressive keyword hints** — instead of leaking the full answer, each question can carry its own comma-separated keywords; asking for a hint reveals them a few at a time.
- **Per-session timer** — a free-running clock in the study header, independent of the server, that resets on every new or restarted session.
- **Search, via Scout** — book search on the library page runs through Laravel Scout's `collection` driver (portable to SQLite, no external index to run).
- **Token-based access** — no registration or passwords. A 4-digit code, minted via Artisan, unlocks the app for a session. The `access_tokens` table is shared across every app in the river series.

## Tech Stack

- [Laravel](https://laravel.com) 13
- [Livewire](https://livewire.laravel.com) 4 with [Flux](https://fluxui.dev) UI components and [Blaze](https://github.com/livewire/blaze) for optimized rendering
- [Laravel Scout](https://laravel.com/docs/scout) (`collection` driver) for search
- [Tailwind CSS](https://tailwindcss.com) 4 + [Vite](https://vitejs.dev) — colors and type shared with the sibling `river-flasher` app's "Mocha Studio" palette
- [PHPUnit](https://phpunit.de) for testing, [Pint](https://laravel.com/docs/pint) for style, [Larastan](https://github.com/larastan/larastan) for static analysis
- SQLite by default (any Laravel-supported database works)

## Requirements

- PHP 8.3+
- Composer
- Node.js & npm (or Bun — see note below)

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan storage:link
npm run build
```

Or, once dependencies are installed, run the bundled setup script:

```bash
composer run setup
```

> **Note:** on machines without a system `node`/`npm` on `PATH` (only an nvm shell function), use `bun install` / `bun run build` / `bun run dev` instead — Bun reads the same `package.json` scripts and `node_modules`.

## Development

Start the app, queue worker, log watcher, and Vite dev server together:

```bash
composer run dev
```

## Access Tokens

There is no registration UI and no login/logout in the traditional sense — access is granted by a shared 4-digit token. Manage tokens via Artisan:

```bash
php artisan token:generate "Name/label for the token"
php artisan token:list
php artisan token:revoke {id} [--force]
```

Copy the generated code immediately — it is only shown once. Any river-series app accepts the same token.

## Testing

```bash
composer run test
```

This runs config clearing, Pint (style check), Larastan (static analysis), and the PHPUnit test suite. To run just the tests:

```bash
php artisan test --compact
```

## Code Style & Static Analysis

```bash
composer run lint          # fix style issues with Pint
composer run types:check   # run Larastan
```

## Project Structure

Business logic is organized into small, single-responsibility Actions (`app/Actions/`), composed by Orchestrators (`app/Actions/Orchestrators/`) for anything spanning multiple steps. Every Action returns an `App\Support\Outcome` (`success`/`failure`/`noViewMessage`) from inside a `try/catch`, so a failure anywhere in the chain is logged and traceable instead of surfacing as a raw 500. Livewire pages live under `resources/views/pages/`, each paired with a PHP class of the same name. Repeated Tailwind utility combinations are pulled into named classes in `resources/css/app.css`'s `@layer components` block rather than retyped. See `.ai/rules/` for the project's documented conventions.

## License

Licensed under the [MIT license](https://opensource.org/licenses/MIT).
