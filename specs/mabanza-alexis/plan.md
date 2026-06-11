# FightClubPortal Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a secure Symfony portal where members register, wait for admin approval via a console command, finish activation through an emailed validation link and mandatory password setup, then exchange private messages.

**Architecture:** Keep the application as a single Symfony monolith with thin controllers, focused application services, Doctrine entities, and a Symfony Console command for review actions. Symfony UX components will power the registration and password-setup experiences without introducing a separate frontend stack. Security rules will be enforced centrally so unfinished accounts cannot reach the portal until activation is complete.

**Tech Stack:** PHP 8.4, Symfony 8.1, Twig, Symfony UX Live Components, Symfony UX Twig Components, Symfony Security, Symfony Validator, Doctrine ORM, Symfony Mailer, Bootstrap, Docker Compose, PHPUnit, Behat.

---

## Summary

FightClubPortal provides a controlled onboarding flow for secret members: visitors register, an administrator reviews each application from the command line, approved users receive an email validation link, the user creates a password, and only then can the member enter the portal and exchange private messages.

## Technical Context

**Language/Version**: PHP 8.4

**Primary Dependencies**: Symfony Framework, Doctrine ORM, Symfony Security, Symfony Validator, Symfony Mailer, Symfony UX Live Components, Symfony UX Twig Components, Bootstrap, PHPUnit, Behat

**Storage**: PostgreSQL

**Testing**: PHPUnit for units and functional tests; Behat for end-to-end journeys

**Target Platform**: Dockerized web application for local development and evaluation

**Project Type**: Web application with one CLI administration command

**Performance Goals**: Keep the registration, approval, email-link, and password-setup paths responsive for evaluator usage; no high-scale throughput target is required.

**Constraints**: No web admin UI; admin approval must happen only through Symfony Console. Sensitive personal data must be validated server-side and never exposed in fixtures, logs, or sample payloads. The full stack must be reproducible from Docker.

**Scale/Scope**: Single application scope with four user journeys: registration, admin review, account finalization, and private messaging.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- Architecture stays simple and SOLID: controllers and commands orchestrate, services own business rules, and UX components are used only where they add clear value.
- Naming is explicit and domain-oriented across production code, fixtures, and tests.
- PHP uses strict typing and explicit signatures at public service boundaries.
- Registration, approval, password setup, login, and blocked-access paths are covered with failing-first PHPUnit and Behat tests.
- Sensitive personal data is validated server-side and excluded from logs, fixtures, and sample payloads.
- The feature is reproducible from a documented Docker Compose setup.
- The handoff artifacts are identified up front: README updates, architecture notes, schema, and diagrams.

## Project Structure

### Documentation (this feature)

```text
specs/mabanza-alexis/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── admin-review-command.md
│   └── member-journey.md
└── tasks.md
```

### Source Code (repository root)

```text
config/
├── packages/
└── routes/

docker/
├── nginx/
└── php/

migrations/

src/
├── Command/
├── Controller/
├── Entity/
├── Form/
├── Message/
├── Repository/
├── Security/
├── Service/
└── Twig/

templates/
├── auth/
├── registration/
├── portal/
└── components/

tests/
├── Behat/
├── Functional/
└── Unit/

features/
```

**Structure Decision**: Keep business logic in `src/Service`, `src/Security`, and `src/Command`; keep controllers and components thin; keep persistence in Doctrine entities and repositories; keep Behat scenarios in `features/` for the end-to-end journey.

## Complexity Tracking

No constitution violations require justification.

## Phase 0 Research

### research.md

- Symfony 8.1 on PHP 8.4 because the brief requires Symfony, Symfony UX, Bootstrap, PHPUnit, and Behat, and Symfony gives all required web, mail, security, and validation primitives in one framework.
- PostgreSQL with Doctrine ORM because the domain is relational and requires strong uniqueness and lifecycle constraints.
- Symfony UX Twig Components and Live Components because the registration and password setup pages need guided interactions without introducing a separate frontend framework.
- Symfony Console because the brief explicitly forbids a web admin UI and the approval workflow belongs outside the browser.
- Symfony Mailer with a local mail catcher because the validation email is part of the critical path and must be visible in local development.
- PHPUnit plus Behat because the brief explicitly calls for unit and functional coverage, and the access gate is best verified as a full journey.

## Phase 1 Design

### data-model.md

- RegistrationApplication captures the candidate submission, sensitive identity data, fighter identity, selected starter, and review status.
- Member represents the approved and activated portal user with an internal identifier and password state.
- PasswordSetupToken represents the single-use email link that moves an approved user into password creation.
- PrivateMessage stores member-to-member messages with sender, recipient, body, and timestamps.
- Explicit state transitions model pending, approved, rejected, awaiting-password, active, and token lifecycle states.

### contracts/admin-review-command.md

- Document the console command syntax for reviewing a pending application by identifier.
- Document the expected approve and reject outcomes, including duplicate-processing refusal.

### contracts/member-journey.md

- Document the public registration route, validation email landing route, password setup route, portal route, and private messaging route.
- Document the rule that unfinished accounts stay blocked from the portal until password creation completes.

### quickstart.md

- Describe the Docker-based setup and database preparation commands.
- Describe the review command used to approve or reject a registration.
- Describe the manual validation journey from registration to password setup.
- Describe how to run PHPUnit and Behat.

## Phase 1 Re-check

- The architecture remains a single Symfony application with focused boundaries.
- The CLI-only approval rule is preserved.
- The critical journeys are covered by PHPUnit and Behat.