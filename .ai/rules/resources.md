---
paths:
  - 'resources/**'
---

# Resources

## Mocha Studio design system (shared with river-flasher) + npm→bun
Colors and font are the "Mocha Studio" palette copied verbatim from the sibling `river-flasher` app's `resources/css/app.css`, so the two apps look like one suite — surface/primary/secondary/tertiary/error tokens, and the `@layer theme { .dark { --color-zinc-*; --color-accent*; } }` block that remaps Flux's hardcoded `dark:zinc-*` classes (modals, navlists, etc.) onto the same palette instead of generic Tailwind gray. Font is Inter everywhere — `font-sans`/`font-heading`/`font-mono` all point to the same Inter stack (single-font, matching river-flasher; there is no separate display/mono typeface). The type scale/spacing tokens (`--text-headline-*`/`--text-body-*`/`--text-label-*`, `--spacing-gutter`/`--spacing-container-padding-*`) still mirror the "Macchiato Soft-Tech" mockups at ~/Downloads/stitch_recall_proximity_tracker (DESIGN.md + one code.html/screen.png per screen), only the color/font tokens changed. When either app's palette changes, update the other to keep them harmonized.

This machine has no system `node`/`npm` on PATH (only an nvm shell function unavailable to non-interactive shells) — use `bun run build` / `bun run dev` instead (bun reads the same package.json scripts and node_modules). Forgetting to rebuild after adding new Tailwind classes is exactly what caused the "everything is blank" bug once — public/build becomes stale and none of the newly-used utility classes exist in the compiled CSS.

## Reusable `@apply` component classes in `app.css`
When a utility combo repeats 2+ times verbatim (or near-verbatim, modulo one-off extra classes) across blade views, pull it into a named class in `resources/css/app.css`'s `@layer components` block instead of retyping it — check that block before writing a new multi-class combo, a matching class may already exist. Current set: `breadcrumb-item` / `breadcrumb-item-current` (flux breadcrumb items — the trailing `!` matters, it overrides Flux's own link color classes), `label-sm` / `label-md` (`font-mono text-label-*`, used on buttons and small mono labels), `heading-md` (`font-heading text-headline-md text-on-surface`), `text-subtitle` (page subtitle under an h1), `text-muted-sm` (`font-sans text-body-sm text-on-surface-variant`), `modal-narrow` (`w-full md:w-96`, the standard small-form modal width), `modal-actions` (the Cancelar/Salvar button row), `panel-muted` (`bg-surface-container-low` inset panel). Extra one-off utilities (`truncate`, `mb-2`, `group-hover:text-on-surface`, etc.) still get added alongside the component class on the element — `@layer components` sits below `utilities` in Tailwind v4's cascade, so they compose safely.
