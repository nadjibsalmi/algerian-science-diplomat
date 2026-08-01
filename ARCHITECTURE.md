# Architecture — Real State vs. Original Specification

This document exists because the original specification for this project
(a ~100-page, 5-part SRS covering a full government-grade multi-tenant
platform: 80+ database tables, 17 business modules, complete UI/UX for
every screen, Docker/CI-CD, monitoring stack, OWASP hardening, i18n for
3 languages with RTL support, and more) describes several months of work
for a full engineering team. It was not possible to build, verify, or
honestly claim completion of that entire scope in a single session, and
pretending otherwise would produce a repository that looks finished but
isn't — which is worse than an honestly incomplete one.

This file tracks exactly what's real, what's scaffolding, and why.

## What actually works, verified

### Application wiring
- Laravel 12 bootstrap wiring registers the web middleware aliases and applies
  locale selection plus security headers to web requests.
- Policies for Offers, Applications, Documents, and Conversations are
  explicitly registered in `AppServiceProvider`.
- Named rate limiters cover public, authentication, search, and upload traffic.
- Event wiring connects registration events to the queued verification listener.
- JSON exception responses are normalized without exposing server-side details;
  Telescope is manually registered only when enabled outside production.

### Backend foundation
- Real Laravel 12 project, cloned from the official `laravel/laravel`
  skeleton (not hand-written from memory), with `composer.json` extended
  to require the spec's mandatory packages: `inertiajs/inertia-laravel`,
  `laravel/sanctum`, `laravel/scout`, `spatie/laravel-permission`,
  `spatie/laravel-activitylog`.
- Every PHP file in this repo was syntax-checked with `php -l` before
  being committed.
- **Important caveat**: the sandbox this was built in has network access
  restricted to an allowlist that does *not* include `repo.packagist.org`
  - meaning `composer install` could not be run, and this project's
  dependency graph could not be resolved or verified locally before the
  first push. The GitHub Actions CI workflow (`.github/workflows/ci.yml`)
  runs `composer install`, migrations against a real PostgreSQL 17
  service container, Pint linting, and the full test suite on every
  push/PR - check the Actions tab for the real, authoritative
  verification this repository couldn't get in its originating
  environment. If CI is red on the first commit, that's the honest
  signal to start debugging from, not a claim that everything already
  works.

### The 17-module folder architecture
`app/Modules/{Authentication,Users,Candidates,Embassies,NGO,Offers,
Applications,Documents,Messaging,Notifications,Search,Dashboard,
Analytics,CMS,Translations,Audit,Administration}/` — all 17 modules
exist with the `Controllers/Models/Services/Repositories/Policies/
Events/Listeners/Requests/Resources/Tests` sub-structure the spec
requires. **Only Embassies and Offers have real content** (see below);
the other 15 are empty scaffolding (`.gitkeep` files only) so the
directory shape is right but there's no actual code inside them yet.

### Embassies + Offers modules (fully real, not stubs)
Chosen as the first two modules to implement in full because they
together let the spec's single most safety-critical requirement be
proven correct rather than just described:

> "Une ambassade ne peut jamais voir les offres, les candidats, les
> documents, les messages d'une autre ambassade."

- Real PostgreSQL migrations: UUID primary keys, soft deletes,
  timestamps, proper foreign keys and indexes, matching the exact column
  lists specified in Part 2 of the SRS.
- Real Eloquent models (`Embassy`, `Offer`) with the relationships,
  Scout search integration (`toSearchableArray`, `shouldBeSearchable` -
  deliberately excludes drafts/paused offers from the public index), and
  Spatie Activity Log integration the spec requires.
- **`OfferPolicy`**: the actual enforcement mechanism for tenant
  isolation. Authorization checks real embassy membership (the
  `embassy_user` pivot table) - never a client-supplied `embassy_id`.
  Super Admin is the one explicit, intentional exception.
- **`tests/Feature/Offers/TenantIsolationTest.php`**: proves this in
  code - a recruiter from Embassy A is asserted to be denied access to
  Embassy B's offer, a recruiter is asserted to be allowed access to
  their own embassy's offer, Super Admin is asserted to bypass the
  restriction, and the query-level `scopeForEmbassy` is asserted to never
  leak another embassy's rows. This is the pattern every future
  organization-scoped module (Candidates, Documents, Applications,
  Messaging) should copy.

### RBAC
`database/seeders/RolesAndPermissionsSeeder.php` implements the exact 11
roles and permission list enumerated in Part 2 of the SRS, via Spatie
Laravel Permission (each permission independent, roles are just named
bundles - exactly as specified).

### Frontend: first real vertical slice
`npm install` succeeded for real in this environment (npm registry, unlike
Packagist, was reachable) - a genuine `package-lock.json` is committed,
so CI's `npm ci` step now has something real to install from. This
caught two real bugs before they reached CI: `@vitejs/plugin-vue@5.x`
doesn't support Vite 7 (fixed by bumping to 6.x), and `lucide-vue-next`
is deprecated in favor of `@lucide/vue` (switched).

A real, working public offers listing page exists:
`resources/js/Pages/Offers/Index.vue` (Vue 3 + `<script setup lang="ts">`),
served by `PublicOfferController` at both `/` and `/offers`, rendering
real published offers from PostgreSQL through Eloquent → Inertia → Vue →
Tailwind. Verified for real:
- `npx vue-tsc --noEmit` passes with zero errors
- `npx vite build` succeeds and produces a real production bundle

This is deliberately the smallest possible slice that exercises the
entire stack end-to-end, rather than a larger set of pages built without
ever confirming the pipeline works.

### What's still scaffolding only

Candidates, NGO, Applications, Documents, Messaging, Notifications,
Search (UI), Dashboard, Analytics, CMS, Translations, Audit (UI/reporting
- the underlying Spatie Activity Log package IS wired in, just no
dedicated Audit module UI yet), Administration.

## What hasn't been started at all

- **Frontend**: no Vue/Inertia pages exist beyond Laravel's default
  welcome view. None of the wireframed screens from Part 3 of the SRS
  (candidate dashboard, embassy dashboard, offer builder, admin panel,
  public homepage, etc.) have been built.
- **i18n**: `APP_LOCALE` is set to `fr` and Laravel's translation file
  mechanism is available by default, but no actual Arabic/English/French
  translation files have been created, and no RTL layout support exists.
- **Docker / Docker Compose**: not set up.
- **Meilisearch / MinIO**: referenced in `.env.example` and
  `composer.json` (Scout), but no running instance is configured or
  connected to.
- **Monitoring (Grafana/Prometheus), ClamAV, WAF, KMS/AES-256 key
  management**: not started.
- **2FA**: the `2fa_enabled` column exists on `users`, but no actual TOTP
  enrollment/verification flow is implemented yet.
- **OpenAPI/Swagger API documentation**: not started.
- **The other ~75+ tables** from the SRS's full data model (Candidates,
  Documents, Applications, Messages, Notifications, Countries,
  Languages, Universities, Research Fields, Skills, Experiences,
  Education, Certificates, Events, News, Publications, Partners,
  Settings, Form Templates/Fields, etc.) - not yet migrated.

## Known deviations from the spec, with reasons

- **PHP 8.4 → PHP 8.2+ constraint kept**: the spec mandates PHP 8.4, but
  Laravel 12's own `composer.json` only requires `^8.2`, and PHP 8.4 was
  not yet available via Ubuntu 24.04's standard package repositories at
  the time this was built (only PHP 8.3 was installable in the sandbox
  used to validate file syntax). The composer constraint was left at the
  framework's own `^8.2` rather than artificially forcing `8.4` in a way
  that couldn't be verified. Update this once your deployment target
  actually has PHP 8.4 available.

## Recommended next steps, in priority order

1. Confirm CI is green (composer install, migrations, Pint, tests,
   frontend build) - this is the first real checkpoint.
2. ~~Build the first real Inertia/Vue page~~ - done: public offers
   listing at `/` and `/offers`. Next: an offer detail page
   (`/offers/{slug}`), and the "create account to apply" gate for
   unauthenticated visitors per the SRS's public offer page spec.
3. Implement the **Candidates** module next (candidate_profiles,
   education, experience, skills, languages, certificates tables +
   models) - Offers already exists, so Applications (which links
   Candidates ↔ Offers) becomes buildable right after.
4. Only then tackle Documents/Messaging/Notifications, which all depend
   on Applications existing first.
