# Quickstart - FightClubPortal

## Prerequisites

- Docker and Docker Compose installed
- A local checkout of the repository

## Installation

1. Clone the repository and enter the project directory.

```bash
git clone <URL_DU_DEPOT>
cd test-technique
```

2. Copy the example environment file.

```bash
cp .env.example .env.local
```

## Environment Setup

3. Start the application stack.

```bash
docker compose up -d --build
```

All required configuration (running `composer install`, waiting for the database server, creating the database, and running the Doctrine migrations) is automatically completed on container startup. A single `docker compose up -d --build` command is sufficient.

Once the containers are up, open the app at:

- [http://localhost:8000](http://localhost:8000)

You can also run these manually if needed:

```bash
# Manual installation and migration commands:
docker compose exec php composer install
docker compose exec php bin/console doctrine:database:create --if-not-exists
docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
```

4. Open Mailpit to inspect validation emails.

```bash
open http://localhost:8025
```

5. List registration requests from the CLI.

```bash
docker compose exec php bin/console app:list-registrations
docker compose exec php bin/console app:list-registrations pending --limit=10
```

## Flux d'inscription

1. Se rendre sur [http://localhost:8080/register](http://localhost:8080/register)

2. Remplir le formulaire d'inscription

3. Un administrateur valide l'inscription via la commande CLI

4. L'utilisateur reçoit un email avec un lien de validation

5. L'utilisateur crée son mot de passe

6. L'utilisateur accède au portail

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