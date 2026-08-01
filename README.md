# Algerian Science Diplomat

Algerian Science Diplomat is a Laravel and Vue platform for discovering and managing international scientific, academic, and professional opportunities for Algerian students, researchers, engineers, and professionals.

## Project status

This repository contains an active foundation for the platform. The current implementation includes the Laravel 12 backend, Vue 3 and TypeScript frontend foundations, role-based access control, embassy and opportunity workflows, and the initial multi-tenant data policies. Additional modules remain under development.

## Technology

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Vue 3, TypeScript, Inertia.js, Vite, and Tailwind CSS
- **Database:** PostgreSQL
- **Authentication:** Laravel Sanctum
- **Authorization:** Spatie Laravel Permission
- **Search:** Laravel Scout with Meilisearch configuration
- **Audit logging:** Spatie Activity Log

## Getting started

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure the database values in .env
php artisan migrate --seed
npm install
npm run dev
```

Run the test suite with:

```bash
php artisan test
```

## Data isolation

Organization-scoped data must be protected by real organization membership and authorization policies. A client-supplied organization identifier must never be treated as proof of access. New modules should follow the existing policy and test patterns.

## Documentation

- [Architecture](ARCHITECTURE.md) — current scope and technical decisions
- [Contributing](CONTRIBUTING.md) — contribution guidelines

## License

MIT. See [LICENSE](LICENSE).
