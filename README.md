# Symfony User Registration API

[![CI](https://github.com/mortogo321/symfony-coding-test/actions/workflows/test.yml/badge.svg)](https://github.com/mortogo321/symfony-coding-test/actions/workflows/test.yml)
[![Symfony](https://img.shields.io/badge/Symfony-7.4_LTS-black?logo=symfony)](https://symfony.com/releases/7.4)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php)](https://www.php.net/releases/8.4/)
[![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?logo=mysql)](https://dev.mysql.com/doc/relnotes/mysql/8.4/en/)
[![RabbitMQ](https://img.shields.io/badge/RabbitMQ-4.3-FF6600?logo=rabbitmq)](https://www.rabbitmq.com/release-information)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

A take-home coding exercise implementing a Symfony API endpoint that validates input, persists a record to MySQL, and publishes an asynchronous message via RabbitMQ. Demonstrates request validation with DTOs, Doctrine ORM, Symfony Messenger, centralized API error handling, and PHPUnit-based controller testing.

> **Sep-2026 refresh:** Symfony `7.3` (EOL Jan-2026) → `7.4 LTS` (supported to Nov-2029; `8.1` standard-support ends Jan-2027, so LTS is the enterprise pick), PHP 8.4, MySQL `8.0` → `8.4`, RabbitMQ `3` → `4.3`, pinned Docker images, non-root runtime + `HEALTHCHECK`, fixed `APP_SECRET` regeneration bug (now fails fast in prod when unset), duplicate-email `409` handling, `GET /api/health`, CI split into lint + test + docker build (`checkout@v7`), Dependabot, MIT license.

## What's inside

- `POST /api/register-user` endpoint validating full name, email, and phone number
- Request payload mapped to a validated DTO (`RegisterUserRequest`) via Symfony's `MapRequestPayload`
- `User` entity persisted through Doctrine ORM with a migration for the `users` table
- Duplicate email returns `409 Conflict` with `{"status":"error","message":"Email is already registered"}` (DB unique constraint + explicit catch)
- `GET /api/health` liveness endpoint (used by Docker `HEALTHCHECK` and compose `depends_on`)
- Asynchronous message dispatch on successful registration via Symfony Messenger (RabbitMQ/AMQP transport), handled by a dedicated message handler
- Centralized API exception listener returning consistent JSON error responses (validation details on `422`, generic message on `5xx` — no internal leakage)
- PHPUnit test suite covering the success path, validation failures, duplicate email, and health check
- GitHub Actions CI: lint (`composer validate` + `php -l`) + test (MySQL 8.4 + RabbitMQ 4.3 services) + docker build (dev + prod targets, compose validation)

## Tech stack

- PHP 8.4, Symfony 7.4 LTS
- Doctrine ORM / Doctrine Migrations, MySQL 8.4
- Symfony Messenger with AMQP transport, RabbitMQ 4.3
- PHPUnit 11/12
- Docker (pinned `php:8.4-cli-bookworm`, `composer:2.8`, `mysql:8.4`, `rabbitmq:4.3-management`), non-root runtime

## Quickstart

Requires Docker and Docker Compose.

```bash
docker compose -f docker/docker-compose.yml up --build
```

The API is available at `http://localhost:8080`.

- Health: `GET http://localhost:8080/api/health` → `{"status":"ok"}`
- Register: `POST http://localhost:8080/api/register-user` with `{"fullName","email","phone"}` → `201` on success, `422` on validation failure, `409` on duplicate email

Run the test suite:

```bash
docker exec -it app php bin/phpunit
```

Production (requires a real secret):

```bash
APP_SECRET=$(openssl rand -hex 32) docker compose -f docker/docker-compose.prod.yml up --build
```

## Structure

```
docker/            docker-compose files (dev and prod) and container entrypoint
symfony/           Symfony application
  src/Controller/Api/  API controllers (UserController, HealthController)
  src/DTO/         Request validation DTOs
  src/Entity/      Doctrine entities
  src/Message/     Messenger message classes
  src/MessageHandler/  Messenger message handlers
  src/EventListener/   API exception listener (consistent JSON errors)
  tests/           PHPUnit tests
```

## Key endpoints

`POST /api/register-user`

Accepts `fullName`, `email`, and `phone`; returns `201` with the created user on success, `422` with per-field validation errors on failure, or `409` when the email is already registered.

`GET /api/health`

Returns `{"status":"ok","service":"symfony-coding-test"}`. Used by Docker healthchecks.
