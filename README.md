# Coding Test — Symfony API + RabbitMQ + PHPUnit

## Goal
Create a Symfony endpoint:

POST `/api/register-user`

You should:
- Validate incoming data: Fullname, Email, Phone Number, 
- Save a User entity in MySQL
- Publish a RabbitMQ message (via Symfony Messenger)
- Include a PHPUnit test for the controller
- Run fully via Docker on localhost port 8080 

---

# How the project should be run

```bash
docker-compose up --build
```

# How the project should be test

```bash
docker exec -it app php bin/phpunit
```