# Quickstart - FightClubPortal

## Prerequisites

- Docker and Docker Compose installed
- A local checkout of the repository

## Environment Setup

1. Start the application stack.

```bash
docker compose up -d --build
```

2. Install PHP dependencies inside the application container.

```bash
docker compose exec php composer install
```

3. Create and migrate the database.

```bash
docker compose exec php bin/console doctrine:database:create --if-not-exists
docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
```

4. Open Mailpit to inspect validation emails.

```bash
open http://localhost:8025
```

## Validation Scenarios

### Scenario 1: Candidate registration

1. Open the registration page in the browser.
2. Fill in all required fields with valid values.
3. Submit the form.

**Expected outcome:** The request is stored as pending and no portal access is granted yet.

### Scenario 2: Admin review from the command line

1. Find the pending request identifier.
2. Approve it from the console.

```bash
docker compose exec php bin/console app:review-registration 1 --decision=approve
```

**Expected outcome:** The request becomes approved, a member account is created, and a validation email is queued.

### Scenario 3: Email validation link

1. Open the validation email in Mailpit.
2. Follow the link.

**Expected outcome:** The browser lands on the password creation page, not the portal home.

### Scenario 4: Password gate

1. Attempt to open a protected portal page before creating the password.
2. Complete the password creation step.

**Expected outcome:** Access is blocked before password creation and becomes available after successful completion.

### Scenario 5: Test suite

```bash
docker compose exec php bin/phpunit
docker compose exec php vendor/bin/behat
```

**Expected outcome:** PHPUnit passes for services, command flow, and functional coverage; Behat passes for the registration, approval, activation, and access-gating journeys.