# Bloggers — Laravel + Vue + Inertia

## Stack

- **Backend**: Laravel 13, PHP 8.3, Fortify (auth), Inertia-Laravel 3, Wayfinder
- **Frontend**: Vue 3, TypeScript, Inertia-Vue 3, Tailwind CSS v4, shadcn-vue (new-york-v4), Reka UI, Lucide icons
- **Testing**: Pest 4 (Feature tests use `RefreshDatabase`, SQLite :memory:)
- **Tooling**: Pint (Laravel preset), PHPStan level 7, ESLint, Prettier, vue-tsc, pnpm

## Auth (Fortify)

All auth routes (login, logout, password reset, email verification) are registered by Laravel Fortify — they are **not** in `routes/web.php`.

```php
// config/fortify.php — only these features are enabled
Features::resetPasswords(),
Features::emailVerification(),
```

No registration or 2FA features are enabled.

## Routes & Teams

- Most routes are scoped under `{current_team}` (bound by **slug**, not id).
- Middleware stack: `auth`, `verified`, `EnsureTeamMembership`.
- Auth routes (login, etc.) and settings use separate middleware groups.
- Settings routes are in a separate file (`routes/settings.php`), required from `routes/web.php`.

## Wayfinder (codegen)

`resources/js/{actions,routes,wayfinder}/` is **generated code** and gitignored.

After adding/changing a route:
```
php artisan wayfinder:generate
```
The Vite plugin (`@laravel/vite-plugin-wayfinder`) also handles this during dev.

Import route functions from `@/routes` (not `@/wayfinder`):
```ts
import { dashboard, login, home } from '@/routes'
```

## Commands

| Command | What it does |
|---|---|
| `composer dev` | Starts 3 concurrent processes: `artisan serve` + `artisan queue:listen` + `npm run dev` |
| `composer test` | Runs `lint:check` → `types:check` (PHPStan) → `php artisan test` (Pest) |
| `php artisan test` | Runs Pest directly (skips lint/type checks) |
| `composer lint` | `pint --parallel` |
| `composer types:check` | `phpstan analyse` (level 7) |
| `npm run types:check` | `vue-tsc --noEmit` |
| `npm run format` | `prettier --write resources/` |
| `npm run lint` | `eslint . --fix` |
| `npm run build:ssr` | Build SSR bundle (`vite build && vite build --ssr`) |

## Testing (Pest 4)

```bash
# Run all tests
php artisan test

# Run a specific test file
php artisan test tests/Feature/DashboardTest.php

# Run with filter
php artisan test --filter="guests are redirected"
```

Feature tests use `RefreshDatabase` automatically (configured in `tests/Pest.php`). Tests use SQLite in-memory (`phpunit.xml`).

## Frontend conventions

- **Layout routing** (`resources/js/app.ts`): `auth/*` pages → `AuthLayout`, `settings/*` and `teams/*` → `AppLayout + SettingsLayout`, `Welcome` → no layout, rest → `AppLayout`.
- **Utility**: `cn()` from `@/lib/utils` (tailwind-merge + clsx).
- **Components**: shadcn-vue UI components in `@/components/ui/`. CVA for variants.
- **Toasts**: Flash toasts via `vue-sonner` — listen for Inertia flash events.
- **Dark mode**: CSS variables with `.dark` class (set via `useAppearance` composable).
- **Aliases** (`@/` → `resources/js/`): configured in `tsconfig.json`.
- **Prettier**: 4-space indent, single quotes, prettier-plugin-tailwindcss aware of `clsx`/`cn`/`cva`.

## Important quirks

- `.npmrc` has `ignore-scripts=true` — npm lifecycle scripts won't run.
- pnpm is used (see `pnpm-workspace.yaml`). Hoists `@inertiajs/core` publicly.
- Inertia SSR is **enabled** (`config/inertia.php`). Expects a process at `127.0.0.1:13714`.
- Tailwind CSS v4 uses `@import 'tailwindcss'` (no `tailwind.config.js`).
- `boost.json` configures Laravel Boost skills (Fortify, Wayfinder, Pest, Inertia-Vue, Tailwind, etc.).
- No CI workflows exist (`.github/workflows/` is empty).
- Tab width: 4 spaces (except YAML: 2), enforced by `.editorconfig`.
