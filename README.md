# Symfony User Registration API

A take-home coding exercise implementing a Symfony API endpoint that validates input, persists a record to MySQL, and publishes an asynchronous message via RabbitMQ. Demonstrates request validation with DTOs, Doctrine ORM, Symfony Messenger, centralized API error handling, and PHPUnit-based controller testing.

## What's inside

- `POST /api/register-user` endpoint validating full name, email, and phone number
- Request payload mapped to a validated DTO (`RegisterUserRequest`) via Symfony's `MapRequestPayload`
- `User` entity persisted through Doctrine ORM with a migration for the `users` table
- Asynchronous message dispatch on successful registration via Symfony Messenger (RabbitMQ/AMQP transport), handled by a dedicated message handler
- Centralized API exception listener returning consistent JSON error responses (including validation error details)
- PHPUnit test suite covering the success path and validation failure cases (missing/invalid fields)
- GitHub Actions workflow running the test suite against MySQL and RabbitMQ services

## Tech stack

- PHP, Symfony framework
- Doctrine ORM / Doctrine Migrations, MySQL
- Symfony Messenger with AMQP transport, RabbitMQ
- PHPUnit
- Docker Compose (separate dev and production configurations)

## Quickstart

Requires Docker and Docker Compose.

```bash
docker compose -f docker/docker-compose.yml up --build
```

The API is available at `http://localhost:8080`.

Run the test suite:

```bash
docker exec -it app php bin/phpunit
```

A production-oriented compose file is also provided at `docker/docker-compose.prod.yml`.

## Structure

```
docker/            docker-compose files (dev and prod) and container entrypoint
symfony/           Symfony application
  src/Controller/  API controllers
  src/DTO/         Request validation DTOs
  src/Entity/      Doctrine entities
  src/Message/     Messenger message classes
  src/MessageHandler/  Messenger message handlers
  src/EventListener/   API exception listener
  tests/           PHPUnit tests
```

## Key endpoint

`POST /api/register-user`

Accepts `fullName`, `email`, and `phone`; returns `201` with the created user on success, or `422` with per-field validation errors on failure.
