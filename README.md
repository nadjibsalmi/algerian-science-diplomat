# Algerian Science Diplomat (ASD)

A government-grade platform centralizing international scientific,
academic, and professional opportunities for Algerian students,
researchers, engineers, and professionals — published by embassies and
international organizations present in Algeria, with candidates applying
through a single unified profile.

## ⚠️ Project status: early foundation, not production-ready

This repository is a **real, working foundation**, not a finished
product. Full details on what's implemented vs. planned are in
[`ARCHITECTURE.md`](ARCHITECTURE.md) — please read it before assuming any
feature exists. In short:

- ✅ Real Laravel 12 + Vue 3 + TypeScript + Inertia + Tailwind project,
  installable and buildable (verified via CI on every push, since this
  couldn't be verified in the sandbox that generated the initial
  commit — see ARCHITECTURE.md for why).
- ✅ The full 17-module folder architecture from the spec exists.
- ✅ Two modules (**Embassies**, **Offers**) are implemented end-to-end:
  migrations, models, RBAC, and — critically — **the multi-tenant
  isolation policy is implemented and covered by real tests** that prove
  one embassy can never access another's data.
- ❌ The other 15 modules exist as empty folder scaffolding only
  (Candidates, Documents, Messaging, Notifications, Search UI, CMS,
  Analytics, etc.) — no controllers, no UI, no business logic yet.
- ❌ No frontend pages exist yet (no Vue components beyond the Laravel
  default welcome page).
- ❌ Docker, Meilisearch, MinIO, Grafana/Prometheus, ClamAV, i18n/RTL,
  OWASP hardening pass — none of this is set up yet.

## Tech stack

- **Backend**: Laravel 12, PHP 8.2+ (PHP 8.4 per the original spec once
  it's available in mainstream distro package managers — see
  ARCHITECTURE.md)
- **Frontend**: Vue 3, TypeScript, Inertia.js, Vite, Tailwind CSS v4
- **Database**: PostgreSQL 17
- **Auth**: Laravel Sanctum
- **RBAC**: Spatie Laravel Permission
- **Audit logging**: Spatie Activity Log
- **Search**: Laravel Scout (Meilisearch driver configured, not yet wired to a running instance)

## Getting started

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure DB_* in .env for your local PostgreSQL instance
php artisan migrate --seed
npm install
npm run dev
```

Run tests:

```bash
php artisan test
```

The seeded Super Admin account: `admin@asd.dz` (password set by the
factory's default — change immediately in any real environment).

## Multi-tenant isolation (read this if you're contributing)

The single most important architectural rule in this project:

> An embassy or NGO must never be able to see another organization's
> offers, candidates, documents, or messages.

This is enforced in `app/Modules/Offers/Policies/OfferPolicy.php` via
real embassy membership (the `embassy_user` pivot table), never via a
client-supplied ID. See
`tests/Feature/Offers/TenantIsolationTest.php` for the tests that prove
this holds. **Any new module that scopes data by organization must
follow this same pattern** — a policy checking real membership, backed by
a test that tries to break it.

## Documentation

- [`ARCHITECTURE.md`](ARCHITECTURE.md) — real vs. planned scope, design
  decisions, and why certain spec requirements haven't been verified yet
- [`CONTRIBUTING.md`](CONTRIBUTING.md) — how to contribute

## License

MIT
