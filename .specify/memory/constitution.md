<!--
Sync Impact Report
Version change: 1.0.0 -> 2.0.0
Modified principles:
- Modular Symfony Boundaries -> SOLID and Simple Architecture
- Test the Critical Journey First -> Readable Tests for Critical Journeys
- Security and PII by Default -> Security and PII by Default
- Reproducible Containerized Delivery -> Reproducible Containerized Delivery
- Documented Handoff and Minimal Surface Area -> Explicit Naming and Clean Code
Added sections:
- none
Removed sections:
- none
Templates requiring updates:
- ✅ updated .specify/templates/plan-template.md
- ✅ updated .specify/templates/spec-template.md
- ✅ updated .specify/templates/tasks-template.md
- ✅ checked .specify/templates/commands/*.md (directory absent)
Follow-up TODOs:
- TODO(RATIFICATION_DATE): Original ratification date is not available from the repository context.
-->

# Test Technique Constitution

## Core Principles

### I. SOLID and Simple Architecture
Application code MUST follow SOLID principles while keeping the architecture simple:
controllers orchestrate, services and use cases own business rules, and Symfony UX
components focus on presentation. Every new abstraction MUST solve a concrete need;
layers, patterns, or indirection added "just in case" are forbidden.

Rationale: the test evaluates architecture quality and decision-making, so code
must be modular without becoming over-engineered.

### II. Explicit Naming and Clean Code
Names for classes, methods, variables, test cases, and fixtures MUST be explicit
and domain-oriented. Code and tests MUST be readable without hidden conventions:
small functions, limited branching, no dead code, no duplicated intent, and no
ambiguous abbreviations.

Rationale: explicit naming and clean structure reduce review friction and make
handoff faster during the final presentation.

### III. Strict Types and Contracts
All PHP code MUST declare strict types and use explicit parameter, return, and
property types whenever possible. Public service boundaries (commands, handlers,
DTOs, repositories, and domain services) MUST define clear contracts through typed
interfaces or concrete signatures.

Rationale: strict typing catches defects early and keeps architectural boundaries
predictable as features evolve.

### IV. Readable Tests for Critical Journeys
Any change affecting registration, validation, approval, password setup, or login
MUST include failing-first tests at the lowest useful level and one functional
journey test. Test code MUST follow the same standards as production code:
explicit naming, clear setup, and minimal duplication.

Rationale: reliability depends on both coverage and readability; brittle or opaque
tests are not acceptable evidence.

### V. Security and PII by Default
All personal data fields MUST be validated server-side. Sensitive identifiers MUST
not be logged, hard-coded, or committed in fixtures, examples, or sample output.
Authentication and authorization gates MUST block access until the validation and
password setup lifecycle is complete.

Rationale: the domain handles sensitive user information, so safety and access
control are default requirements rather than optional hardening.

### VI. Reproducible Containerized Delivery
The project MUST run from documented Docker instructions and produce the same
behavior from a clean checkout. Any manual setup step MUST be documented and
justified, and local development must remain reproducible without hidden state.

Rationale: the brief requires Docker-based delivery, so a fresh environment must be
able to reach the same result as the authoring machine.

### VII. Documented Handoff and Minimal Surface Area
Each feature MUST ship with the artifacts needed to understand and operate it:
updated README content, relevant architecture notes, and any required diagrams.
New abstractions MUST earn their place; the simplest design that satisfies the
acceptance criteria is preferred.

Rationale: the final presentation is part of the evaluation, and unnecessary
abstraction reduces clarity without improving the test outcome.

## Additional Constraints

- The stack for this project is Symfony, Symfony UX (Twig Components and Live
	Components), Bootstrap, Docker, PHPUnit, and Behat.
- PHP files MUST start with `declare(strict_types=1);` unless a framework-generated
	file makes this technically impossible.
- New classes and tests MUST use explicit, domain-centered names; generic names
	(`Manager`, `Helper`, `Utils`, `Test1`) are forbidden.
- The approval workflow MUST be implemented as a Symfony Console command; do not
	add a web admin interface unless the scope is explicitly changed.
- Registration and login flows MUST preserve the domain fields defined in the brief
	and enforce validation on the server side before persistence or account access.
- Example data, fixtures, and logs MUST avoid real personal data and sensitive
	identifiers.

## Development Workflow

- Work starts from a specification, then a plan, then tasks; each slice MUST remain
	independently testable and shippable.
- Each implementation task MUST state where business logic lives and why that
	location respects SOLID and simple architecture constraints.
- User journeys are prioritized so that the highest-value slice can stand on its
	own as an MVP.
- Any change touching registration, approval, password setup, or login MUST include
	a validation step before it is considered complete.
- Code review and self-review MUST reject changes that reduce naming clarity,
	type safety, or test readability.
- Branches, README updates, and review notes MUST reflect the feature scope and the
	validation performed.

## Governance

This constitution supersedes other project guidance when conflicts arise. Any
amendment MUST update this file, propagate the change to dependent templates, and
record the impact in the sync report.

Versioning follows semantic versioning: MAJOR for principle removals or
redefinitions, MINOR for new principles or materially expanded guidance, and PATCH
for wording clarifications or non-semantic refinements.

Compliance review expectations:
- Every plan and implementation review MUST include a constitution check.
- Violations of the principles above MUST be justified in writing when they are
	unavoidable, and the simpler compliant alternative MUST be documented.
- Changes affecting user journeys MUST retain the required tests, documentation,
	Docker reproducibility expectations, and strict typing expectations.

**Version**: 2.0.0 | **Ratified**: TODO(RATIFICATION_DATE) | **Last Amended**: 2026-06-10
