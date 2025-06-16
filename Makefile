.PHONY: up down test test-init test-unit test-functional

# Запуск Docker контейнеров
up:
	docker compose up -d

# Остановка Docker контейнеров
down:
	docker compose down

# Построение контейнеров
build:
	docker compose build

# Инициализация тестовой базы данных
test-init:
	docker compose exec php php tests/bin/init-test-db.php

# Запуск всех тестов
test: test-unit test-functional

# Запуск unit тестов
test-unit:
	docker compose exec php php vendor/bin/codecept run unit

# Запуск функциональных тестов
test-functional:
	docker compose exec php php vendor/bin/codecept run functional

# Полная пересборка и тесты
test-full: down build up
	sleep 10
	make test-init
	make test

# Логи
logs:
	docker compose logs -f

# Вход в PHP контейнер
shell:
	docker compose exec php bash 