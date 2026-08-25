---
paths:
  - 'database/migrations/*access_tokens*'
---

# Migrations

## access_tokens table is shared across river-* apps
There is no Laravel/Fortify user auth in this app. Auth is a 4-digit code stored (sha256-hashed) in `access_tokens`, exactly mirroring river-flasher and river-notetaker. In production all river-* apps point at the same database, so the `access_tokens` table is shared: the migration guards with `Schema::hasTable('access_tokens')` before creating it and never drops it in `down()`. Every domain model (Book, etc.) scopes to the current visitor via `session('access_token_id')`, not a `user_id`/auth guard. Manage tokens with the `token:generate {name}`, `token:list`, `token:revoke {id}` artisan commands (app/Console/Commands).
