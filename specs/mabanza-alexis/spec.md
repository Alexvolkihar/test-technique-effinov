# Feature Specification: FightClubPortal

**Feature Branch**: `test/mabanza-alexis`

**Created**: 2026-06-11

**Status**: Draft

**Input**: User description: "FightClubPortal est un portail secret permettant à des membres de s'échanger des messages en toute discrétion. Les candidats s'inscrivent via un formulaire dédié, puis une validation administrative hors interface web déclenche l'envoi d'un lien de confirmation par email. Après ouverture de ce lien, l'utilisateur définit son mot de passe avant d'accéder au portail membre."

## Clarifications

### Session 2026-06-11

- Q: Le lien email mène toujours à la création du mot de passe, et tant que ce mot de passe n’est pas créé, le compte reste bloqué hors du portail. → A: Le lien email mène toujours à la création du mot de passe, et tant que ce mot de passe n’est pas créé, le compte reste bloqué hors du portail.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - S'inscrire au portail (Priority: P1)

Un candidat remplit un formulaire d'inscription dédié avec ses informations personnelles et son identité de combattant pour soumettre une demande d'accès.

**Why this priority**: Sans inscription, aucun membre ne peut entrer dans le parcours de validation ou accéder au portail.

**Independent Test**: Compléter le formulaire avec des données valides doit créer une demande en attente, sans donner d'accès immédiat au portail.

**Acceptance Scenarios**:

1. **Given** qu'un visiteur ouvre l'interface d'inscription, **When** il renseigne tous les champs obligatoires avec des valeurs valides et valide le formulaire, **Then** sa demande est enregistrée comme en attente de validation.
2. **Given** qu'un visiteur omet un champ obligatoire ou saisit une valeur invalide, **When** il soumet le formulaire, **Then** la demande est refusée et les erreurs sont affichées avant toute création de compte.

### User Story 2 - Valider une candidature (Priority: P2)

Un administrateur approuve ou refuse une candidature depuis une commande en ligne de commande, sans interface d'administration web.

**Why this priority**: L'accès au portail dépend d'une validation explicite, qui constitue la barrière de sécurité principale.

**Independent Test**: Exécuter la commande de validation sur une candidature en attente doit permettre de la faire passer à l'état approuvé ou refusé, sans interface web.

**Acceptance Scenarios**:

1. **Given** qu'une candidature est en attente, **When** l'administrateur la valide via la commande, **Then** un identifiant interne de membre est attribué et l'utilisateur est préparé pour le parcours de confirmation.
2. **Given** qu'une candidature est déjà traitée, **When** la commande est relancée sur cette même candidature, **Then** le système évite de dupliquer la fiche et signale l'état existant.

### User Story 3 - Finaliser l'accès par email et mot de passe (Priority: P3)

Un candidat approuvé reçoit un lien de validation par email qui mène toujours à la création de son mot de passe avant tout autre accès au portail.

**Why this priority**: Cette étape transforme une candidature approuvée en compte utilisable tout en empêchant tout accès prématuré.

**Independent Test**: Suivre le lien reçu par email doit mener à une page de création de mot de passe, et l'utilisateur ne doit pas pouvoir naviguer ailleurs avant d'avoir terminé cette étape.

**Acceptance Scenarios**:

1. **Given** qu'une candidature est approuvée, **When** l'utilisateur ouvre le lien reçu par email, **Then** il est dirigé vers une page dédiée à la création de son mot de passe.
2. **Given** qu'un utilisateur n'a pas encore créé son mot de passe, **When** il tente d'accéder à une autre page du portail, **Then** l'accès est bloqué tant que cette étape n'est pas terminée.
3. **Given** qu'un utilisateur a créé son mot de passe avec succès, **When** il se reconnecte ou poursuit sa navigation, **Then** il accède au portail membre.

### User Story 4 - Échanger des messages discrètement (Priority: P4)

Un membre actif échange des messages privés avec d'autres membres autorisés dans l'espace secret du portail.

**Why this priority**: C'est la valeur d'usage principale du portail une fois l'accès sécurisé obtenu.

**Independent Test**: Deux membres actifs peuvent envoyer et consulter des messages privés sans exposer ces échanges à des visiteurs non autorisés.

**Acceptance Scenarios**:

1. **Given** qu'un membre actif est connecté, **When** il envoie un message à un autre membre, **Then** le message est visible uniquement par les membres concernés.
2. **Given** qu'un visiteur non authentifié tente d'accéder à l'espace de messagerie, **When** il ouvre une page protégée, **Then** l'accès est refusé.

### Edge Cases

- Un numéro de sécurité sociale ou un numéro d'accréditation déjà utilisé doit empêcher la validation de la candidature.
- Un lien de validation expiré ou déjà consommé doit être refusé et demander un nouveau parcours de validation.
- Une candidature approuvée mais sans mot de passe ne doit donner accès à aucune autre page que l'étape de création du mot de passe.
- Une tentative d'accès au portail par un compte non finalisé doit toujours ramener l'utilisateur vers le parcours de finalisation.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Le système MUST proposer une interface d'inscription dédiée collectant les champs suivants: nom, prénom, adresse, date de naissance, numéro de sécurité sociale, pseudo de combattant, numéro d'accréditation CERFA 666, et Pokémon Starter choisi entre bleu et rouge.
- **FR-002**: Le système MUST refuser toute inscription incomplète ou invalide et afficher des messages d'erreur compréhensibles avant la création d'une demande.
- **FR-003**: Chaque inscription MUST être enregistrée à l'état "en attente" jusqu'à validation explicite par un administrateur.
- **FR-004**: Le système MUST permettre la validation ou le refus d'une candidature uniquement via une commande en ligne de commande, sans interface d'administration web.
- **FR-005**: Lorsqu'une candidature est validée, le système MUST créer une fiche membre avec un identifiant interne unique.
- **FR-006**: Après validation, le système MUST envoyer à l'utilisateur un email contenant un lien de validation personnel.
- **FR-007**: Le système MUST rediriger systématiquement l'utilisateur vers une page de création de mot de passe lorsqu'il ouvre son lien de validation.
- **FR-008**: Tant que le mot de passe n'a pas été défini, le système MUST empêcher l'accès à toute autre page du portail et ne permettre que cette étape de finalisation.
- **FR-009**: Une fois le mot de passe défini, le système MUST autoriser l'accès au portail membre.
- **FR-010**: Le système MUST permettre aux membres actifs d'envoyer et de consulter des messages privés avec d'autres membres autorisés.
- **FR-011**: Le système MUST empêcher les visiteurs non authentifiés ou les comptes non finalisés d'accéder à l'espace de messagerie.
- **FR-012**: Le système MUST protéger les données personnelles sensibles contre toute exposition involontaire aux visiteurs non autorisés.

### Key Entities *(include if feature involves data)*

- **Candidature d'inscription**: Représente la demande soumise par un visiteur, avec ses informations personnelles, son pseudo de combattant, son numéro CERFA 666 et son starter choisi.
- **Membre**: Représente l'utilisateur validé, avec son identifiant interne, son état d'activation et son accès au portail.
- **Lien de validation**: Représente le parcours temporaire envoyé par email pour confirmer l'accès et définir le mot de passe.
- **Message privé**: Représente un échange discret entre membres actifs autorisés.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% des inscriptions incomplètes ou invalides sont refusées avant toute création de compte.
- **SC-002**: 100% des candidatures approuvées reçoivent un lien de validation et une étape de mot de passe avant l'accès au portail.
- **SC-003**: 100% des comptes non finalisés restent bloqués hors du portail jusqu'à la création du mot de passe.
- **SC-004**: Au moins 90% des utilisateurs de test peuvent compléter le parcours inscription -> validation -> mot de passe -> premier message sans assistance.
- **SC-005**: Aucun visiteur non autorisé ne peut lire ou envoyer de message privé lors des scénarios de vérification.

## Assumptions

- Les candidats disposent d'une adresse email valide et consultable pour recevoir le lien de validation.
- L'administrateur utilise exclusivement la commande en ligne de commande prévue pour traiter les candidatures.
- Le portail de messagerie est réservé aux comptes validés et finalisés; aucune fonction publique de messagerie n'est exposée.
- Les doublons de champs sensibles, notamment le numéro de sécurité sociale et le numéro d'accréditation, sont considérés comme des erreurs de validation.
- Les échanges sont privés par défaut et ne doivent pas être visibles en dehors des membres concernés.