# Spencers Wood Village Hall

The hall's website and admin, built with Laravel 13, Filament 5 and
Inertia + Vue 3 + Tailwind 4, hosted on Laravel Forge. It replaces the Nuxt
site (`swvh_nuxt`) and keeps the same pages, addresses and look.

## Getting started

```bash
composer setup     # install, .env, migrate + seed, build
composer dev       # http://localhost:8000
```

Sign in at `/admin` as `admin@swvh.test` / `password` (local only).

## Checks

```bash
composer test          # Pint, Larastan, Pest
npm run check          # front-end lint and format
npm run types:check    # vue-tsc
```

## More

- `docs/spec.md`: what the site does, the rules and the env vars.
- `docs/admin.md`: the admin.
- `docs/deploy.md`: deploying to Forge, mail through Resend, and moving the
  data across from the Nuxt site.
