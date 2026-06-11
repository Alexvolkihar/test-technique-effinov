# Tasks: FightClubPortal

**Input**: Design documents from `/specs/mabanza-alexis/`

**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Unit, functional, and Behat tests are included as they cover constitution-critical flows such as registration, approval, password setup, login, security, and Docker validation.

**Quality**: Tasks MUST preserve SOLID with simple architecture, explicit naming, and strict typing for PHP code. The same readability standard applies to test code.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Single project**: `src/`, `tests/` at repository root
- Paths shown below assume single project.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [x] T001 Initialize Symfony project per plan.md structure in repository root
- [x] T002 Set up Docker Compose environment with PHP 8.4, PostgreSQL, Nginx, and Mailpit in docker-compose.yml
- [x] T003 [P] Configure PHPUnit and Behat testing tools in phpunit.xml.dist and behat.yml
- [x] T004 Install and configure Composer dependencies in composer.json
- [x] T005 [P] Setup frontend assets configuration with Bootstrap in assets/

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [ ] T006 Configure database connection and migrations structure in config/packages/doctrine.yaml
- [ ] T007 Configure mailer credentials and transport in config/packages/mailer.yaml
- [ ] T008 Configure security framework, firewalls, and hashing in config/packages/security.yaml
- [ ] T009 Create base user security class mapping to MemberAccount in src/Security/User.php

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - S'inscrire au portail (Priority: P1) 🎯 MVP

**Goal**: Candidate fills out a registration form with sensitive/fighter details, saving a pending request.

**Independent Test**: Submitting the form with valid data creates a pending request without granting portal access.

### Tests for User Story 1

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [ ] T010 [P] [US1] Write unit tests for RegistrationRequest entity validation in tests/Unit/Entity/RegistrationRequestTest.php
- [ ] T011 [P] [US1] Write Behat scenarios for registration form submission in features/registration.feature

### Implementation for User Story 1

- [ ] T012 [P] [US1] Create RegistrationRequest Doctrine entity and repository in src/Entity/RegistrationRequest.php and src/Repository/RegistrationRequestRepository.php
- [ ] T013 [US1] Create RegistrationRequest database migration in migrations/
- [ ] T014 [US1] Implement registration form component using Symfony UX in src/Twig/Components/RegistrationForm.php and templates/components/RegistrationForm.html.twig
- [ ] T015 [US1] Implement registration controller and routes in src/Controller/RegistrationController.php and templates/registration/register.html.twig
- [ ] T016 [US1] Add custom validators for uniqueness of SSN and CERFA 666 in src/Validator/UniqueRegistrationField.php and src/Validator/UniqueRegistrationFieldValidator.php

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently

---

## Phase 4: User Story 2 - Valider une candidature (Priority: P2)

**Goal**: Admin approves or rejects a registration request from Symfony Console command.

**Independent Test**: Running the console review command on a pending request changes status and prepares member account + queues validation email.

### Tests for User Story 2

- [ ] T017 [P] [US2] Write unit tests for RegistrationReviewService in tests/Unit/Service/RegistrationReviewServiceTest.php
- [ ] T018 [P] [US2] Write functional tests for review command in tests/Functional/Command/ReviewRegistrationCommandTest.php

### Implementation for User Story 2

- [ ] T019 [P] [US2] Create MemberAccount entity and repository in src/Entity/MemberAccount.php and src/Repository/MemberAccountRepository.php
- [ ] T020 [P] [US2] Create ValidationToken entity and repository in src/Entity/ValidationToken.php and src/Repository/ValidationTokenRepository.php
- [ ] T021 [US2] Create MemberAccount and ValidationToken migration in migrations/
- [ ] T022 [US2] Implement RegistrationReviewService containing approval and rejection business rules in src/Service/RegistrationReviewService.php
- [ ] T023 [US2] Implement console review command in src/Command/ReviewRegistrationCommand.php
- [ ] T024 [US2] Create ValidationEmailSender service to send personal validation link in src/Service/ValidationEmailSender.php

**Checkpoint**: At this point, User Stories 1 AND 2 should both work independently

---

## Phase 5: User Story 3 - Finaliser l'accès par email et mot de passe (Priority: P3)

**Goal**: Approved member opens validation link, sets password, and account state transitions to active.

**Independent Test**: Following the valid token link directs user to password creation, blocks navigation to portal, and activates account once password is set.

### Tests for User Story 3

- [ ] T025 [P] [US3] Write unit and functional tests for token verification and password creation in tests/Functional/Controller/PasswordSetupControllerTest.php
- [ ] T026 [P] [US3] Write Behat scenarios for validation link click and password setup journey in features/password_setup.feature

### Implementation for User Story 3

- [ ] T027 [US3] Implement token verification logic and redirect in src/Controller/ValidationController.php
- [ ] T028 [US3] Implement password setup page with form validation in src/Controller/PasswordSetupController.php and templates/auth/password_setup.html.twig
- [ ] T029 [US3] Implement security voter or subscriber to block all portal access for awaiting_password accounts in src/Security/PasswordSetupRequiredSubscriber.php
- [ ] T030 [US3] Update User provider and authentication configuration to support login after activation in src/Security/MemberProvider.php and config/packages/security.yaml

**Checkpoint**: All user stories should now be independently functional

---

## Phase 6: User Story 4 - Échanger des messages discrètement (Priority: P4)

**Goal**: Active members exchange private messages in the secure portal area.

**Independent Test**: Two active members can send and read private messages, while anonymous/unactivated/other users are blocked.

### Tests for User Story 4

- [ ] T031 [P] [US4] Write functional tests for private messaging in tests/Functional/Controller/MessageControllerTest.php
- [ ] T032 [P] [US4] Write Behat scenarios for secure message exchange in features/private_messaging.feature

### Implementation for User Story 4

- [ ] T033 [P] [US4] Create Message entity and repository in src/Entity/Message.php and src/Repository/MessageRepository.php
- [ ] T034 [US4] Create Message database migration in migrations/
- [ ] T035 [US4] Implement portal home dashboard in src/Controller/PortalController.php and templates/portal/index.html.twig
- [ ] T036 [US4] Implement private messaging controller and UI in src/Controller/MessageController.php and templates/portal/messages.html.twig
- [ ] T037 [US4] Add security authorization checks to ensure only active participants see a message in src/Security/MessageVoter.php

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [ ] T038 Update README.md with detailed instructions on running Docker, migrations, testing tools, console command, and Mailpit
- [ ] T039 Create document d'architecture, relational schema, class UML, and flow diagrams under specs/mabanza-alexis/
- [ ] T040 Security audit to verify that no sensitive fields are leaked in logs or error templates
- [ ] T041 Run the entire test suite and quickstart scenarios to validate Docker reproducibility

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3+)**: All depend on Foundational phase completion
  - User stories can then proceed sequentially in priority order (P1 → P2 → P3 → P4) or in parallel if needed.
- **Polish (Final Phase)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational (Phase 2) - No dependencies on other stories
- **User Story 2 (P2)**: Can start after Foundational (Phase 2) - Integrates with US1
- **User Story 3 (P3)**: Can start after Foundational (Phase 2) - Integrates with US1/US2
- **User Story 4 (P4)**: Can start after Foundational (Phase 2) - Integrates with US1/US2/US3

### Within Each User Story

- Tests MUST be written and FAIL before implementation
- Models before services
- Services before endpoints
- Core implementation before integration
- Story complete before moving to next priority

### Parallel Opportunities

- All Setup tasks marked [P] can run in parallel
- All Foundational tasks marked [P] can run in parallel
- Once Foundational phase completes, user stories can start in parallel (if team capacity allows)
- All tests for a user story marked [P] can run in parallel
- Models within a story marked [P] can run in parallel

---

## Parallel Example: User Story 1

```bash
# Launch all models for User Story 1 together:
Task: "Create RegistrationRequest Doctrine entity and repository in src/Entity/RegistrationRequest.php and src/Repository/RegistrationRequestRepository.php"

# Launch all tests for User Story 1 together:
Task: "Write unit tests for RegistrationRequest entity validation in tests/Unit/Entity/RegistrationRequestTest.php"
Task: "Write Behat scenarios for registration form submission in features/registration.feature"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL - blocks all stories)
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: Test User Story 1 independently
5. Deploy/demo if ready

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 → Test independently → Deploy/Demo (MVP!)
3. Add User Story 2 → Test independently → Deploy/Demo
4. Add User Story 3 → Test independently → Deploy/Demo
5. Add User Story 4 → Test independently → Deploy/Demo
6. Each story adds value without breaking previous stories
