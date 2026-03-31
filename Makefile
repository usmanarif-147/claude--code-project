# ─────────────────────────────────────────────────
#  Portfolio — Docker + Laravel Makefile
# ─────────────────────────────────────────────────

# Docker compose shorthand
DC = docker compose
APP = $(DC) exec app

# ─────────────────────────────────────────────────
#  Docker Lifecycle
# ─────────────────────────────────────────────────

up: ## Start all containers
	$(DC) up -d

down: ## Stop all containers
	$(DC) down

restart: ## Restart all containers
	$(DC) down
	$(DC) up -d

build: ## Rebuild images (no cache)
	$(DC) build --no-cache

ps: ## Show running containers
	$(DC) ps

logs: ## Tail logs for all containers
	$(DC) logs -f

logs-app: ## Tail app container logs
	$(DC) logs -f app

logs-nginx: ## Tail nginx container logs
	$(DC) logs -f nginx

logs-db: ## Tail database container logs
	$(DC) logs -f db

shell: ## Shell into app container
	$(APP) bash

db-shell: ## Open MySQL CLI
	$(DC) exec db mysql -u portfolio_user -psecret portfolio

destroy: ## Stop containers + delete volumes (WIPES DB)
	$(DC) down -v

prune: ## Free disk — remove dangling images, containers, networks
	docker system prune -f

# ─────────────────────────────────────────────────
#  Laravel / Artisan
# ─────────────────────────────────────────────────

artisan: ## Run artisan command: make artisan cmd="migrate"
	$(APP) php artisan $(cmd)

migrate: ## Run migrations
	$(APP) php artisan migrate

migrate-fresh: ## Fresh migrate + seed
	$(APP) php artisan migrate:fresh --seed

seed: ## Run seeders
	$(APP) php artisan db:seed

tinker: ## Open tinker REPL
	$(APP) php artisan tinker

routes: ## List all routes
	$(APP) php artisan route:list

routes-filter: ## Filter routes by module: make routes-filter q=blog
	$(APP) php artisan route:list | grep -i "$(q)"

key: ## Generate app key
	$(APP) php artisan key:generate

cache: ## Clear all caches
	$(APP) php artisan optimize:clear

optimize: ## Cache config/routes/views for production
	$(APP) php artisan optimize

# ─────────────────────────────────────────────────
#  Code Quality
# ─────────────────────────────────────────────────

test: ## Run test suite
	$(APP) php artisan test

pint: ## Fix code style
	$(APP) ./vendor/bin/pint

pint-check: ## Check code style (no fix)
	$(APP) ./vendor/bin/pint --test

# ─────────────────────────────────────────────────
#  Composer
# ─────────────────────────────────────────────────

composer-install: ## Install PHP dependencies
	$(APP) composer install

composer-update: ## Update PHP dependencies
	$(APP) composer update

# ─────────────────────────────────────────────────
#  Frontend (runs on host — npm not in container)
# ─────────────────────────────────────────────────

dev: ## Start Vite dev server
	npm run dev

build-assets: ## Build frontend for production
	npm run build

npm-install: ## Install node dependencies
	npm install

# ─────────────────────────────────────────────────
#  Combo Commands
# ─────────────────────────────────────────────────

setup: ## First-time setup: build, start, install deps, migrate, seed, build assets
	$(DC) build
	$(DC) up -d
	$(APP) composer install
	$(APP) php artisan key:generate
	$(APP) php artisan migrate --seed
	npm install
	npm run build

check: ## Verify everything works: pint + tests + routes + asset build (stops on first failure)
	$(APP) ./vendor/bin/pint --test
	$(APP) php artisan test
	$(APP) php artisan route:list > /dev/null
	npm run build

refresh: ## Restart + clear caches + dump autoload (no data loss)
	$(DC) down
	$(DC) up -d
	$(APP) php artisan optimize:clear
	$(APP) composer dump-autoload

fresh-clean: ## Nuclear reset: wipe DB + uploads, re-seed
	$(APP) php artisan migrate:fresh --seed
	$(APP) php artisan optimize:clear
	rm -rf storage/app/public/blog storage/app/public/projects storage/app/public/testimonials storage/app/public/profiles
	$(APP) php artisan storage:link

# ─────────────────────────────────────────────────
#  Help
# ─────────────────────────────────────────────────

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

.DEFAULT_GOAL := help
.PHONY: up down restart build ps logs logs-app logs-nginx logs-db shell db-shell destroy prune \
        artisan migrate migrate-fresh seed tinker routes routes-filter key cache optimize \
        test pint pint-check composer-install composer-update dev build-assets npm-install \
        setup check refresh fresh-clean help
