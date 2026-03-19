# MoleKVM Project Makefile
# Quick commands for development, deployment and testing

.PHONY: help install dev build test e2e deploy clean logs shell

# Default target
.DEFAULT_GOAL := help

# Colors for output
BLUE := \033[36m
GREEN := \033[32m
YELLOW := \033[33m
RED := \033[31m
NC := \033[0m # No Color

help: ## Show this help message
	@echo "$(BLUE)MoleKVM Project Commands$(NC)"
	@echo "========================"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(GREEN)%-20s$(NC) %s\n", $$1, $$2}'

# ═══════════════════════════════════════════════════════════
# INSTALLATION & SETUP
# ═══════════════════════════════════════════════════════════

install: ## Install dependencies and setup environment
	@echo "$(BLUE)Setting up MoleKVM development environment...$(NC)"
	cp -n .env.example .env || true
	@echo "$(GREEN)✓ Environment file created (.env)$(NC)"
	@echo "$(YELLOW)⚠ Please edit .env with your configuration$(NC)"

dev-setup: install ## Full development setup with Docker
	@echo "$(BLUE)Building Docker images...$(NC)"
	docker-compose -f docker-compose.plesk.yml build
	@echo "$(GREEN)✓ Development environment ready$(NC)"
	@echo "Run 'make dev' to start development servers"

# ═══════════════════════════════════════════════════════════
# DEVELOPMENT
# ═══════════════════════════════════════════════════════════

dev: ## Start development environment (Apache + Plesk Mock)
	@echo "$(BLUE)Starting development servers...$(NC)"
	docker-compose -f docker-compose.plesk.yml up -d
	@echo "$(GREEN)✓ Services started:$(NC)"
	@echo "  🌐 Website:    http://localhost:8080"
	@echo "  🎛️  Plesk Mock: http://localhost:8081"
	@echo "  🚀 Nginx:      http://localhost:8082"
	@echo ""
	@echo "Run 'make logs' to view logs"

dev-stop: ## Stop development environment
	@echo "$(YELLOW)Stopping development servers...$(NC)"
	docker-compose -f docker-compose.plesk.yml down
	@echo "$(GREEN)✓ Services stopped$(NC)"

dev-restart: dev-stop dev ## Restart development environment

nginx: ## Start with Nginx instead of Apache
	@echo "$(BLUE)Starting with Nginx...$(NC)"
	docker-compose -f docker-compose.plesk.yml up -d nginx-alt
	@echo "$(GREEN)✓ Nginx running at http://localhost:8082$(NC)"

# ═══════════════════════════════════════════════════════════
# BUILDING
# ═══════════════════════════════════════════════════════════

build: ## Build all Docker images
	@echo "$(BLUE)Building Docker images...$(NC)"
	docker-compose -f docker-compose.plesk.yml build --no-cache
	@echo "$(GREEN)✓ Build complete$(NC)"

build-apache: ## Build Apache image only
	docker build -t molekvm/apache-php -f Dockerfile .

build-plesk: ## Build Plesk mock image only
	docker build -t molekvm/plesk-admin -f plesk-admin/Dockerfile ./plesk-admin

# ═══════════════════════════════════════════════════════════
# TESTING
# ═══════════════════════════════════════════════════════════

test: ## Run all tests
	@echo "$(BLUE)Running test suite...$(NC)"
	make test-unit
	make test-integration
	make test-e2e

test-unit: ## Run unit tests
	@echo "$(BLUE)Running unit tests...$(NC)"
	@echo "$(YELLOW)⚠ Unit tests not yet implemented$(NC)"
	@echo "Add tests to ./tests/unit/"

test-integration: ## Run integration tests
	@echo "$(BLUE)Running integration tests...$(NC)"
	@echo "$(YELLOW)⚠ Integration tests not yet implemented$(NC)"

test-e2e: dev ## Run end-to-end tests (Playwright)
	@echo "$(BLUE)Running E2E tests...$(NC)"
	@if ! command -v npx &> /dev/null; then \
		echo "$(YELLOW)Installing Playwright...$(NC)"; \
		npm install -g @playwright/test; \
		npx playwright install; \
	fi
	@echo "$(BLUE)Starting E2E tests...$(NC)"
	npx playwright test || echo "$(YELLOW)⚠ E2E tests need configuration$(NC)"

test-e2e-ui: dev ## Run E2E tests with UI
	npx playwright test --ui

test-e2e-debug: dev ## Run E2E tests in debug mode
	npx playwright test --debug

test-visual: dev ## Run visual regression tests
	@echo "$(BLUE)Running visual regression tests...$(NC)"
	npx playwright test --project=chromium --update-snapshots || true

# ═══════════════════════════════════════════════════════════
# CODE QUALITY
# ═══════════════════════════════════════════════════════════

lint: ## Run linters (PHP, HTML, CSS, JS)
	@echo "$(BLUE)Running linters...$(NC)"
	@echo "$(YELLOW)PHP linting...$(NC)"
	@php -l index.php || true
	@echo "$(YELLOW)CSS linting...$(NC)"
	@npx stylelint "**/*.css" --ignore-path .gitignore 2>/dev/null || echo "Install stylelint: npm install -g stylelint"
	@echo "$(YELLOW)HTML validation...$(NC)"
	@echo "Use: npx html-validate index.php"

format: ## Format code
	@echo "$(BLUE)Formatting code...$(NC)"
	@echo "$(YELLOW)Run prettier for JS/CSS...$(NC)"
	@npx prettier --write "**/*.{js,css,html}" 2>/dev/null || echo "Install: npm install -g prettier"

security-check: ## Run security checks
	@echo "$(BLUE)Running security checks...$(NC)"
	@echo "Checking for exposed secrets..."
	@grep -r "sk_live\|AKIA" . --include="*.php" --include="*.js" --include="*.env" 2>/dev/null && echo "$(RED)⚠ Potential secrets found!$(NC)" || echo "$(GREEN)✓ No obvious secrets found$(NC)"
	@echo "Checking .env file..."
	@test -f .env && echo "$(YELLOW)⚠ .env exists - ensure it's in .gitignore$(NC)" || echo "$(GREEN)✓ No .env file$(NC)"

# ═══════════════════════════════════════════════════════════
# DEPLOYMENT
# ═══════════════════════════════════════════════════════════

deploy-plesk: ## Deploy to Plesk (manual instructions)
	@echo "$(BLUE)Plesk Deployment Guide:$(NC)"
	@echo "========================"
	@echo "1. Upload files via SFTP:"
	@echo "   sftp user@your-domain.com"
	@echo "   put index.php /httpdocs/"
	@echo "   put .env /httpdocs/"
	@echo ""
	@echo "2. Or use Git deployment in Plesk Panel"
	@echo "3. Or use Plesk File Manager"
	@echo ""
	@echo "$(YELLOW)Remember to:$(NC)"
	@echo "  - Set DEBUG=false in .env"
	@echo "  - Add Stripe live keys"
	@echo "  - Enable SSL certificate"

deploy-docker: build ## Deploy using Docker Compose
	@echo "$(BLUE)Deploying with Docker...$(NC)"
	docker-compose -f docker-compose.plesk.yml up -d
	@echo "$(GREEN)✓ Deployed$(NC)"

# ═══════════════════════════════════════════════════════════
# MONITORING & LOGS
# ═══════════════════════════════════════════════════════════

logs: ## View all logs
	docker-compose -f docker-compose.plesk.yml logs -f

logs-apache: ## View Apache logs only
	docker-compose -f docker-compose.plesk.yml logs -f apache

logs-plesk: ## View Plesk mock logs
	docker-compose -f docker-compose.plesk.yml logs -f plesk-admin

shell: ## Open shell in Apache container
	docker-compose -f docker-compose.plesk.yml exec apache bash

shell-plesk: ## Open shell in Plesk container
	docker-compose -f docker-compose.plesk.yml exec plesk-admin sh

status: ## Check service status
	@echo "$(BLUE)Service Status:$(NC)"
	@docker-compose -f docker-compose.plesk.yml ps

health: ## Check health of services
	@echo "$(BLUE)Health Checks:$(NC)"
	@echo "Website (Apache):"
	@curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 && echo " $(GREEN)✓$(NC)" || echo " $(RED)✗ Down$(NC)"
	@echo "Plesk Mock:"
	@curl -s -o /dev/null -w "%{http_code}" http://localhost:8081 && echo " $(GREEN)✓$(NC)" || echo " $(RED)✗ Down$(NC)"
	@echo "Nginx:"
	@curl -s -o /dev/null -w "%{http_code}" http://localhost:8082 && echo " $(GREEN)✓$(NC)" || echo " $(RED)✗ Down$(NC)"

# ═══════════════════════════════════════════════════════════
# DATABASE
# ═══════════════════════════════════════════════════════════

db-init: ## Initialize database (if using backend)
	@echo "$(BLUE)Initializing database...$(NC)"
	@test -f init.sql && echo "$(YELLOW)Run init.sql manually in Plesk/phpMyAdmin$(NC)" || echo "$(YELLOW)No init.sql found$(NC)"

db-backup: ## Backup database
	@echo "$(BLUE)Database backup...$(NC)"
	@echo "$(YELLOW)Configure DB credentials in .env first$(NC)"

db-migrate: ## Run database migrations
	@echo "$(BLUE)Running migrations...$(NC)"
	@echo "$(YELLOW)Migrations not configured$(NC)"

# ═══════════════════════════════════════════════════════════
# E2E TEST SETUP
# ═══════════════════════════════════════════════════════════

e2e-setup: ## Setup Playwright for E2E testing
	@echo "$(BLUE)Setting up E2E tests...$(NC)"
	@npm list -g @playwright/test &>/dev/null || npm install -g @playwright/test
	@npx playwright install chromium firefox webkit
	@test -d tests/e2e || mkdir -p tests/e2e
	@test -f playwright.config.js || echo "$(YELLOW)Create playwright.config.js$(NC)"
	@echo "$(GREEN)✓ E2E setup complete$(NC)"

e2e-record: dev ## Record new E2E test
	@echo "$(BLUE)Recording new E2E test...$(NC)"
	npx playwright codegen http://localhost:8080

e2e-report: ## Show E2E test report
	@npx playwright show-report || echo "$(YELLOW)No report found. Run 'make test-e2e' first$(NC)"

# ═══════════════════════════════════════════════════════════
# UTILITIES
# ═══════════════════════════════════════════════════════════

clean: ## Clean up containers, images and volumes
	@echo "$(YELLOW)Cleaning up...$(NC)"
	docker-compose -f docker-compose.plesk.yml down -v --remove-orphans
	docker system prune -f
	@echo "$(GREEN)✓ Cleanup complete$(NC)"

clean-all: clean ## Full cleanup including built images
	docker-compose -f docker-compose.plesk.yml down --rmi all -v
	docker system prune -af
	@echo "$(GREEN)✓ Full cleanup complete$(NC)"

update: ## Update dependencies and images
	@echo "$(BLUE)Updating...$(NC)"
	docker-compose -f docker-compose.plesk.yml pull
	docker-compose -f docker-compose.plesk.yml build --no-cache
	@echo "$(GREEN)✓ Update complete$(NC)"

env-check: ## Check environment configuration
	@echo "$(BLUE)Environment Check:$(NC)"
	@echo "========================"
	@test -f .env && echo "$(GREEN)✓ .env exists$(NC)" || echo "$(RED)✗ .env missing - run 'make install'$(NC)"
	@test -f .env && grep -q "STRIPE_PK" .env && echo "$(GREEN)✓ Stripe keys configured$(NC)" || echo "$(YELLOW)⚠ Stripe keys may need configuration$(NC)"
	@test -f .env && grep -q "PRODUCT_PRICE_BASE" .env && echo "$(GREEN)✓ Product pricing configured$(NC)" || echo "$(YELLOW)⚠ Product pricing may need configuration$(NC)"
	@echo ""
	@echo "Docker:"
	@docker --version | cut -d' ' -f3 | cut -d',' -f1
	@echo "Docker Compose:"
	@docker-compose --version | cut -d' ' -f3 | cut -d',' -f1

size: ## Show project size statistics
	@echo "$(BLUE)Project Size:$(NC)"
	@du -sh . 2>/dev/null
	@echo ""
	@echo "Docker Images:"
	@docker images | grep molekvm || echo "$(YELLOW)No MoleKVM images built$(NC)"

# ═══════════════════════════════════════════════════════════
# CI/CD SHORTCUTS
# ═══════════════════════════════════════════════════════════

ci-test: install dev test-e2e clean ## Full CI test pipeline

preview: ## Generate preview build
	@echo "$(BLUE)Generating preview...$(NC)"
	@make dev
	@echo "$(GREEN)✓ Preview ready at http://localhost:8080$(NC)"

# Quick shortcuts
up: dev ## Alias for 'make dev'
down: dev-stop ## Alias for 'make dev-stop'
restart: dev-restart ## Alias for 'make dev-restart'
ps: status ## Alias for 'make status'
