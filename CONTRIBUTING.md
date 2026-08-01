# Contributing

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev
```

## Before submitting a PR

```bash
./vendor/bin/pint --test
php artisan test
npm run typecheck
npm run build
```

All four must pass - CI enforces this automatically.

## The one rule that matters most

Any new module scoping data by organization (embassy or NGO) **must**
follow the pattern in `app/Modules/Offers/Policies/OfferPolicy.php`:
authorize based on real, persisted membership (a pivot table), never a
client-supplied ID - and back it with a test that actively tries to
access another organization's data and asserts it's denied. See
`tests/Feature/Offers/TenantIsolationTest.php` for the template.

## Scope discipline

Check `ARCHITECTURE.md` before starting work - it tracks what's real vs.
scaffolding. Please update it when you move something from "scaffolding"
to "real" in a PR.
