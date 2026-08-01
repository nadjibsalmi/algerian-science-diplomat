.PHONY: install dev prod test shell queue horizon migrate seed fresh lint fix typecheck build help

# ─── Colours ──────────────────────────────────────────────────────────────────
GREEN  := \033[0;32m
YELLOW := \033[1;33m
NC     := \033[0m

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(GREEN)%-20s$(NC) %s\n", $$1, $$2}'

# ─── Installation ─────────────────────────────────────────────────────────────
install: ## Installation complète (première fois)
	@echo "$(YELLOW)Installing dependencies...$(NC)"
	docker compose run --rm app composer install --no-interaction --prefer-dist
	docker compose run --rm app npm install
	@if [ ! -f .env ]; then cp .env.example .env; fi
	docker compose run --rm app php artisan key:generate --ansi
	$(MAKE) migrate
	$(MAKE) seed
	docker compose run --rm app npm run build
	@echo "$(GREEN)Installation complete!$(NC)"

install-dev: ## Installation en mode développement
	@echo "$(YELLOW)Installing dev dependencies...$(NC)"
	docker compose -f docker-compose.yml -f docker-compose.dev.yml run --rm app composer install
	docker compose -f docker-compose.yml -f docker-compose.dev.yml run --rm app npm install
	@if [ ! -f .env ]; then cp .env.example .env; fi
	docker compose -f docker-compose.yml -f docker-compose.dev.yml run --rm app php artisan key:generate --ansi
	$(MAKE) migrate
	$(MAKE) seed
	@echo "$(GREEN)Dev installation complete!$(NC)"

# ─── Démarrage ────────────────────────────────────────────────────────────────
dev: ## Démarre tous les services en mode développement
	docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d
	@echo "$(GREEN)Services démarrés :$(NC)"
	@echo "  App:        http://localhost"
	@echo "  Mailpit:    http://localhost:8025"
	@echo "  MinIO:      http://localhost:9001"
	@echo "  Meilisearch: http://localhost:7700"

prod: ## Démarre tous les services en mode production
	docker compose up -d --build
	docker compose run --rm app php artisan config:cache
	docker compose run --rm app php artisan route:cache
	docker compose run --rm app php artisan view:cache
	docker compose run --rm app php artisan event:cache
	@echo "$(GREEN)Production démarrée$(NC)"

stop: ## Arrête tous les services
	docker compose down

restart: ## Redémarre tous les services
	$(MAKE) stop
	$(MAKE) dev

# ─── Base de données ──────────────────────────────────────────────────────────
migrate: ## Lance les migrations
	docker compose run --rm app php artisan migrate --force

migrate-fresh: ## Recrée la BDD depuis zéro + seed
	docker compose run --rm app php artisan migrate:fresh --seed --force

seed: ## Lance les seeders
	docker compose run --rm app php artisan db:seed --force

fresh: ## Alias pour migrate-fresh (dev)
	$(MAKE) migrate-fresh

rollback: ## Annule la dernière migration
	docker compose run --rm app php artisan migrate:rollback

# ─── Tests ────────────────────────────────────────────────────────────────────
test: ## Lance tous les tests PHPUnit
	docker compose run --rm app php artisan test --parallel

test-coverage: ## Lance les tests avec rapport de couverture
	docker compose run --rm app php artisan test --coverage --min=80

test-filter: ## Lance un test spécifique : make test-filter FILTER=TenantIsolation
	docker compose run --rm app php artisan test --filter=$(FILTER)

pest: ## Lance les tests Pest
	docker compose run --rm app ./vendor/bin/pest --parallel

# ─── Qualité de code ──────────────────────────────────────────────────────────
lint: ## Vérifie le style PHP (Pint) et JS (ESLint)
	docker compose run --rm app ./vendor/bin/pint --test
	docker compose run --rm app npm run lint

fix: ## Corrige automatiquement le style PHP et JS
	docker compose run --rm app ./vendor/bin/pint
	docker compose run --rm app npm run lint:fix

typecheck: ## TypeScript type-check strict
	docker compose run --rm app npm run type-check

stan: ## PHPStan niveau 8
	docker compose run --rm app ./vendor/bin/phpstan analyse --level=8

audit: ## Audit de sécurité des dépendances
	docker compose run --rm app composer audit
	docker compose run --rm app npm audit

build: ## Build assets Vite pour la production
	docker compose run --rm app npm run build

# ─── Shell / Debug ────────────────────────────────────────────────────────────
shell: ## Ouvre un shell dans le conteneur app
	docker compose exec app bash

tinker: ## Ouvre Laravel Tinker
	docker compose exec app php artisan tinker

logs: ## Affiche les logs de l'application
	docker compose exec app php artisan pail

# ─── Queue / Horizon ──────────────────────────────────────────────────────────
queue: ## Démarre les workers de queue
	docker compose exec app php artisan queue:work --tries=3 --timeout=90

horizon: ## Ouvre le dashboard Horizon
	@echo "$(GREEN)Horizon dashboard: http://localhost/horizon$(NC)"
	docker compose exec app php artisan horizon:status

horizon-pause: ## Pause Horizon
	docker compose exec app php artisan horizon:pause

horizon-resume: ## Reprend Horizon
	docker compose exec app php artisan horizon:continue

# ─── Cache ────────────────────────────────────────────────────────────────────
cache-clear: ## Vide tous les caches
	docker compose exec app php artisan optimize:clear

cache-warm: ## Précauffe les caches (prod)
	docker compose exec app php artisan config:cache
	docker compose exec app php artisan route:cache
	docker compose exec app php artisan view:cache
	docker compose exec app php artisan event:cache

# ─── Meilisearch ──────────────────────────────────────────────────────────────
search-index: ## Indexe toutes les données dans Meilisearch
	docker compose exec app php artisan scout:import "App\Modules\Offers\Models\Offer"

search-flush: ## Vide l'index Meilisearch
	docker compose exec app php artisan scout:flush "App\Modules\Offers\Models\Offer"

# ─── Maintenance ──────────────────────────────────────────────────────────────
down: ## Active le mode maintenance
	docker compose exec app php artisan down --retry=60 --secret=$(SECRET)

up: ## Désactive le mode maintenance
	docker compose exec app php artisan up
