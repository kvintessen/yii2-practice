.DEFAULT_GOAL := help

DC := docker compose
PHP := $(DC) exec php
PHP_RUN := $(DC) run --rm php

.PHONY: help setup env build install up down stop restart logs ps sh \
        migrate migrate-down seed test test-unit test-functional test-acceptance \
        codecept-build static cs cs-fix clean

help: ## Показать список команд
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}'

setup: env build install up migrate seed codecept-build ## Полное развёртывание проекта с нуля
	@echo ""
	@echo "Готово! Приложение доступно на http://127.0.0.1:8080"

env: ## Создать .env из .env.example, если он ещё не существует
	@test -f .env || cp .env.example .env

build: ## Собрать docker-образы
	$(DC) build

install: ## Установить зависимости composer (создаёт vendor/ и cookieValidationKey)
	$(PHP_RUN) composer install

up: ## Поднять стек (nginx + php-fpm + postgres) в фоне
	$(DC) up -d

down: ## Остановить стек и удалить контейнеры (данные БД сохраняются)
	$(DC) down

stop: ## Остановить контейнеры без удаления
	$(DC) stop

restart: down up ## Перезапустить стек

logs: ## Смотреть логи всех сервисов
	$(DC) logs -f

ps: ## Статус контейнеров
	$(DC) ps

sh: ## Открыть shell в контейнере php
	$(PHP) sh

migrate: ## Применить миграции (создаёт таблицы, включая user)
	$(PHP) php yii migrate --interactive=0

migrate-down: ## Откатить последнюю миграцию
	$(PHP) php yii migrate/down --interactive=0

seed: ## Наполнить БД демо-данными (admin/admin, demo/demo, каталог, заказы)
	$(PHP) php yii seed/all

codecept-build: ## Пересобрать Support/_generated actor-классы Codeception
	$(PHP) vendor/bin/codecept build

test: ## Прогнать все тесты (Unit, Functional, Acceptance)
	$(PHP) composer tests

test-unit: ## Прогнать только Unit-тесты
	$(PHP) vendor/bin/codecept run Unit --env php-builtin

test-functional: ## Прогнать только Functional-тесты
	$(PHP) vendor/bin/codecept run Functional --env php-builtin

test-acceptance: ## Прогнать только Acceptance-тесты
	$(PHP) vendor/bin/codecept run Acceptance --env php-builtin

static: ## Статический анализ (phpstan)
	$(PHP) composer static

cs: ## Проверка code style (phpcs)
	$(PHP) composer cs

cs-fix: ## Автофикс code style (phpcbf)
	$(PHP) composer cs-fix

clean: ## Остановить стек и удалить volume с данными БД (полный сброс)
	$(DC) down -v
