# Research Notes - FightClubPortal

## 1. Symfony 8.1 with Doctrine ORM and PostgreSQL

- **Decision:** Use Symfony 8.1, Doctrine ORM, and PostgreSQL for the application core.
- **Rationale:** The feature is a relational workflow with user lifecycle states, validation tokens, and private messages. Doctrine plus PostgreSQL gives strong support for constraints, lifecycle queries, and readable entities without overengineering the solution.
- **Alternatives considered:** SQLite was rejected because the brief expects a reproducible application with realistic relational constraints; MySQL was viable but PostgreSQL better fits strict constraints and richer relational checks.

## 2. Symfony UX for registration and password setup

- **Decision:** Use Symfony UX Twig Components and Live Components for the registration and password-setup screens.
- **Rationale:** The UI needs a dedicated form flow with stateful interactions, but the scope is small enough that Symfony UX gives the best balance between interactivity and low complexity.
- **Alternatives considered:** Plain Twig controllers were simpler but would make the form interactions less expressive; a full frontend framework would add unnecessary surface area for a test project.

## 3. Console command for admin approval

- **Decision:** Expose the approval workflow through a Symfony Console command that can approve or reject a registration request.
- **Rationale:** The brief explicitly forbids a web admin UI. A console command keeps the admin action auditable, deterministic, and easy to cover with command tests.
- **Alternatives considered:** Separate approve/reject commands were rejected because they duplicate parsing and error handling; a hidden admin route was rejected because it contradicts the requirements.

## 4. Email validation with Symfony Mailer and Mailpit

- **Decision:** Send validation emails through Symfony Mailer and capture them in Mailpit within Docker for local validation.
- **Rationale:** The validation link is part of the critical path and must be testable end-to-end. Mailpit gives a deterministic local mailbox without relying on external infrastructure.
- **Alternatives considered:** Logging mail to files was rejected because it weakens the validation flow; a real SMTP provider was unnecessary for the scope of the test.

## 5. PHPUnit plus Behat for tests

- **Decision:** Use PHPUnit for service, entity, command, and controller-level tests, and Behat for the registration-to-login journey.
- **Rationale:** PHPUnit covers the business rules well, while Behat is the best fit for the full browser journey and account-locking behavior described in the spec.
- **Alternatives considered:** PHPUnit-only coverage was rejected because it would not adequately exercise the full user journey; browser-only tests were rejected because they would make business-rule feedback slower and noisier.

## 6. Account lifecycle states

- **Decision:** Model the user lifecycle with explicit states: pending review, approved pending password, active, and rejected.
- **Rationale:** The password gate is a hard access boundary, so explicit states make the security rules readable and testable.
- **Alternatives considered:** Boolean flags were rejected because they blur lifecycle transitions and make access control harder to reason about.