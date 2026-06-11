---
description: "Generated tasks for FightClubPortal feature"
---

# Tasks: FightClubPortal

**Input**: Design documents in specs/mabanza-alexis/ (plan.md, spec.md, data-model.md, contracts/)

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization, Docker and basic repo config

- [ ] T001 [P] Create Symfony project skeleton (composer.json, config/, src/, templates/) at repository root
- [ ] T002 [P] Add Docker Compose with PHP, Nginx, Postgres and Mailpit in docker/ and docker-compose.yml
- [ ] T003 [P] Add basic README quickstart and update specs/mabanza-alexis/quickstart.md
- [ ] T004 [P] Add project Makefile with common commands (`make start`, `make test`) at repository root
- [ ] T005 [P] Configure GitHub CI placeholders for `phpunit` and `behat` (./.github/workflows/ci.yml)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infra that blocks user stories (DB, auth, mailer, migrations)

- [ ] T006 Setup Doctrine and initial migration configuration (config/packages/doctrine.yaml, migrations/)
- [ ] T007 [P] Implement base security system and user provider (src/Security/, config/packages/security.yaml)
- [ ] T008 [P] Configure Symfony Mailer and local mail catcher wiring (config/packages/mailer.yaml, docker/mailpit)
- [ ] T009 Create core entities: `src/Entity/RegistrationApplication.php`, `src/Entity/Member.php`, `src/Entity/PasswordSetupToken.php`, `src/Entity/PrivateMessage.php`
- [ ] T010 [P] Add repositories and basic Doctrine mappings for the core entities (src/Repository/)
- [ ] T011 [P] Add database fixtures and example data for local testing (tests/fixtures/)
- [ ] T012 Create database migration files for core entities (migrations/)
- [ ] T013 [P] Implement common services: `src/Service/RegistrationService.php`, `src/Service/MemberService.php`, `src/Service/MessagingService.php`
- [ ] T014 Implement a console command skeleton `src/Command/ReviewRegistrationCommand.php` wired in services.yaml
- [ ] T015 [P] Add basic PHPUnit and Behat config files (phpunit.xml, behat.yml) and CI integration tests placeholders

---

## Phase 3: User Story 1 - S'inscrire au portail (Priority: P1) 🎯 MVP

**Goal**: Provide public registration form that creates a `RegistrationApplication` in pending state

**Independent Test**: Submitting valid form creates a pending `RegistrationApplication`; invalid submission shows errors and does not persist.

### Tests
- [ ] T016 [P] [US1] Add functional PHPUnit test for registration form at tests/Functional/RegistrationTest.php
- [ ] T017 [P] [US1] Add Behat scenario for registration journey in features/registration.feature

### Implementation
- [ ] T018 [P] [US1] Create registration form type `src/Form/RegistrationApplicationType.php`
- [ ] T019 [US1] Create controller `src/Controller/RegistrationController.php` with `GET/POST /register` and template `templates/registration/register.html.twig`
- [ ] T020 [US1] Add server-side validators for SSN and CERFA uniqueness and field constraints (src/Validator/ or Doctrine unique constraints)
- [ ] T021 [US1] Persist `RegistrationApplication` on valid submission (src/Controller/ and src/Service/RegistrationService.php)
- [ ] T022 [US1] Add user-facing flash messages and form error handling in templates

**Checkpoint**: Registration produces a pending application and no portal access.

---

## Phase 4: User Story 2 - Valider une candidature (Priority: P2)

**Goal**: Implement CLI-only admin review to approve or reject applications and trigger email for approved ones

**Independent Test**: Running the console command against a pending application marks it approved or rejected and avoids double-processing.

### Tests
- [ ] T023 [P] [US2] Add PHPUnit test for `ReviewRegistrationCommand` in tests/Unit/Command/ReviewRegistrationCommandTest.php

### Implementation
- [ ] T024 [US2] Implement `src/Command/ReviewRegistrationCommand.php` to approve/reject by registration ID (contract: bin/console app:review-registration <id> --decision=approve|reject)
- [ ] T025 [US2] Implement `src/Service/ReviewService.php` that the command uses to change state, create `Member` on approve, and create `PasswordSetupToken`
- [ ] T026 [US2] On approval, send validation email with a single-use token using `src/Message/SendValidationEmailMessage.php` or direct Mailer call; template `templates/emails/validation.html.twig`
- [ ] T027 [US2] Ensure idempotency checks: command refuses to re-process already-handled applications

**Checkpoint**: Admin can approve via CLI and approved candidates receive an email with a token.

---

## Phase 5: User Story 3 - Finaliser l'accès par email et mot de passe (Priority: P3)

**Goal**: Provide the validation link flow that forces password creation and unblocks portal access

**Independent Test**: Visiting the emailed link leads to password creation page; until password set, portal pages are blocked.

### Tests
- [ ] T028 [P] [US3] Add functional PHPUnit test for token consumption and password setup at tests/Functional/PasswordSetupTest.php
- [ ] T029 [P] [US3] Add Behat scenario for the full approved→email→password setup journey in features/password_setup.feature

### Implementation
- [ ] T030 [US3] Create route and controller `src/Controller/PasswordSetupController.php` with template `templates/registration/password_setup.html.twig`
- [ ] T031 [US3] Implement single-use `PasswordSetupToken` consumption, expiry checks, and password hashing (src/Service/TokenService.php)
- [ ] T032 [US3] Enforce access guard: until Member has a password, any portal route redirects to password setup (src/Security/PasswordCompletionVoter or firewall/guard)
- [ ] T033 [US3] Add UI and success redirect to portal after password set

**Checkpoint**: Approved users can set password and then access the portal; un-finalized accounts are blocked.

---

## Phase 6: User Story 4 - Échanger des messages discrètement (Priority: P4)

**Goal**: Implement private messaging between active members

**Independent Test**: Two active members can send/receive messages; non-authenticated users cannot access messaging.

### Tests
- [ ] T034 [P] [US4] Add integration PHPUnit test for messaging flow at tests/Functional/MessagingTest.php

### Implementation
- [ ] T035 [P] [US4] Create `src/Entity/PrivateMessage.php` and repository for message queries
- [ ] T036 [US4] Implement messaging service `src/Service/MessagingService.php` and controller `src/Controller/MessagingController.php`
- [ ] T037 [US4] Add templates `templates/portal/messages/*` and protect routes with security rules

**Checkpoint**: Messaging between active members is functional and access-controlled.

---

## Phase 7: Polish & Cross-Cutting Concerns

- [ ] T038 [P] Write documentation updates in specs/mabanza-alexis/README.md and update `quickstart.md`
- [ ] T039 [P] Add more unit tests and increase coverage for services
- [ ] T040 [P] Security review and hardening: verify no PII in logs/fixtures and enforce validators
- [ ] T041 Run quickstart validation: start Docker, run migrations, perform registration→approve→password→message scenario (document steps in `quickstart.md`)

---

## Dependencies & Execution Order

- Setup (T001..T005) → Foundational (T006..T015) → User Stories (T016..T037) → Polish (T038..T041)
- Within each story: Tests (T016,T017...) should be written before implementation tasks in that story and run to fail-first

## Parallel opportunities identified

- Setup tasks T001..T005 are parallelizable ([P])
- Foundational tasks T007, T008, T010, T011, T013 are parallelizable
- Tests for stories (T016,T017,T023,T028,T029,T034) can be implemented in parallel with non-dependent model tasks
- Different user stories (US1..US4) can be implemented in parallel once foundational tasks complete

## Implementation strategy (MVP)

- MVP scope: deliver Phase 1 + Phase 2 + Phase 3 (US1) to have a working registration flow and pending applications
- Next increment: add US2 and US3 to enable approval and finalization
