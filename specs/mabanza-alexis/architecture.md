# Architecture et Conception Technique - FightClubPortal

Ce document décrit l'architecture globale, le schéma de base de données relationnel, les diagrammes de classes et les flux d'exécution du projet **FightClubPortal**.

---

## 🏗 Architecture Logicielle

Le projet est conçu comme un monolithe Symfony modulaire, structuré selon les principes SOLID :
* **Contrôleurs et Commandes** : Responsables de la gestion des requêtes HTTP (minces) et CLI.
* **Services Applicatifs** : Contiennent les règles métiers (ex: `RegistrationReviewService`, `ValidationEmailSender`).
* **Modèle de Données (Doctrine ORM)** : Entités riches avec contraintes de validation (`Assert\Regex`, `LessThanOrEqual`, etc.).
* **Composants UI (Twig/Bootstrap)** : Composants réutilisables pour le frontend.
* **Cadre de Sécurité** : Pare-feux Symfony (`security.yaml`), authentification basée sur la session, et contrôles d'accès par Voter (`MessageVoter`) et Subscriber (`PasswordSetupRequiredSubscriber`).

---

## 📊 Schéma Relationnel (Database)

Voici le schéma relationnel des tables SQL représenté en notation Mermaid ERD :

```mermaid
erDiagram
    REGISTRATION_REQUESTS ||--o| MEMBER_ACCOUNTS : "crée"
    MEMBER_ACCOUNTS ||--o| VALIDATION_TOKENS : "possède"
    MEMBER_ACCOUNTS ||--o{ MESSAGES : "envoie / reçoit"

    REGISTRATION_REQUESTS {
        int id PK
        string first_name
        string last_name
        text address
        date birth_date
        string social_security_number "Unique"
        string fighter_nickname
        string fighter_certification_number "Unique"
        string pokemon_starter
        string email_address
        string status "pending | approved | rejected"
        text review_note
        datetime reviewed_at
    }

    MEMBER_ACCOUNTS {
        int id PK
        int registration_request_id FK
        string display_number "Unique (FC-XXXXX)"
        string email_address "Unique"
        string password_hash
        string status "awaiting_password | active"
        datetime approved_at
        datetime password_set_at
    }

    VALIDATION_TOKENS {
        int id PK
        int member_account_id FK
        string token_hash
        datetime expires_at
        datetime consumed_at
    }

    MESSAGES {
        int id PK
        int sender_id FK
        int recipient_id FK
        text body
        datetime created_at
    }
```

---

##  UML Class Diagram

Diagramme de classe des entités principales de l'application :

```mermaid
classDiagram
    class RegistrationRequest {
        +int id
        +string firstName
        +string lastName
        +string address
        +DateTime birthDate
        +string socialSecurityNumber
        +string fighterNickname
        +string fighterCertificationNumber
        +string pokemonStarter
        +string emailAddress
        +string status
        +string reviewNote
        +DateTime reviewedAt
        +isPending() bool
    }

    class MemberAccount {
        +int id
        +RegistrationRequest registrationRequest
        +string displayNumber
        +string emailAddress
        +string passwordHash
        +string status
        +DateTime approvedAt
        +DateTime passwordSetAt
        +getNickname() string
    }

    class ValidationToken {
        +int id
        +MemberAccount memberAccount
        +string tokenHash
        +DateTime expiresAt
        +DateTime consumedAt
        +isExpired() bool
    }

    class Message {
        +int id
        +MemberAccount sender
        +MemberAccount recipient
        +string body
        +DateTime createdAt
    }

    MemberAccount "1" --> "1" RegistrationRequest
    ValidationToken "*" --> "1" MemberAccount
    Message "*" --> "2" MemberAccount
```

---

## 🔄 Flux d'Exécution et Séquences

### 1. Inscription et Validation d'un Nouveau Membre
Ce flux décrit les étapes depuis l'inscription d'un candidat jusqu'à l'activation complète de son compte :

```mermaid
sequenceDiagram
    autonumber
    actor Candidat
    actor Admin
    participant App as Application Symfony
    participant DB as Base de Données
    participant SMTP as Mailpit (SMTP)

    Candidat->>App: Remplit le formulaire d'inscription (register)
    App->>DB: Enregistre la demande (statut = pending)
    Note over App,DB: Demande stockée en attente de vérification
    Admin->>App: Lance la commande app:review-registration --decision=approve
    App->>DB: Met à jour la demande (statut = approved)
    App->>DB: Crée le compte membre (statut = awaiting_password)
    App->>DB: Génère un token de validation unique
    App->>SMTP: Envoie l'e-mail d'activation avec le lien
    Candidat->>SMTP: Récupère l'e-mail et clique sur le lien
    Candidat->>App: Accède à la page de mot de passe (/validation/{token})
    Candidat->>App: Soumet son nouveau mot de passe
    App->>DB: Met à jour le compte membre (statut = active)
    Note over Candidat,App: Le membre est redirigé vers le portail
```

### 2. Échange de Messages Sécurisés
Ce flux décrit la façon dont les messages sont séparés en discussions dans la messagerie chiffrée :

```mermaid
sequenceDiagram
    autonumber
    actor Tyler as Tyler (Membre A)
    participant Ctrl as MessageController
    participant Voter as MessageVoter
    participant DB as Base de Données
    actor Marla as Marla (Membre B)

    Tyler->>Ctrl: Accède à /messages
    Ctrl->>DB: Récupère l'historique des discussions impliquant Tyler
    DB-->>Ctrl: Renvoie tous les messages de Tyler
    Note over Ctrl: Construit la liste des contacts actifs (Marla, Jack...)
    Ctrl->>Ctrl: Sélectionne le contact courant (ex: Marla)
    Ctrl->>DB: Récupère la discussion secrète Tyler <-> Marla
    DB-->>Ctrl: Liste des messages
    loop Pour chaque message
        Ctrl->>Voter: Tyler a-t-il le droit de lire ce message ?
        Voter-->>Ctrl: OUI ( Tyler est expéditeur ou destinataire )
    end
    Ctrl-->>Tyler: Affiche le fil de discussion avec Marla et la barre latérale
```

---

## 🔒 Audit de Sécurité et Prévention des Fuites de Données

Un audit de sécurité a été réalisé pour garantir la confidentialité des données sensibles (comme le numéro de sécurité sociale et le numéro d'accréditation CERFA-666) :

1. **Aucun Journal de Données Sensibles (Logs)** :
   * Les logs applicatifs (configurés via le framework standard de Symfony) ne contiennent aucune référence aux champs d'identité sensibles (`socialSecurityNumber`, `fighterCertificationNumber`, `address`, `birthDate`).
   * La configuration de base de données ORM (`doctrine.yaml`) désactive le traçage des paramètres de requêtes SQL (`profiling_collect_backtrace` uniquement si `kernel.debug` est actif, et totalement désactivé en production).
2. **Obfuscation des Messages d'Erreur (Unicité)** :
   * Pour éviter que des attaquants puissent deviner l'existence de comptes de combattants par force brute sur le numéro de sécurité sociale ou d'accréditation, les contraintes d'unicité renvoient un message générique et opaque :
     `"Une demande d'inscription existe déjà pour les informations fournies."`
3. **Sécurisation des URL et Protection des E-mails** :
   * Les adresses e-mail de tous les membres sont masquées dans les interfaces de discussion et remplacées par leurs pseudonymes (`fighterNickname`), protégeant ainsi l'identité des membres actifs dans le portail.
   * L'accès au portail et aux discussions chiffrées est strictement contrôlé par un Voter (`MessageVoter`) qui interdit à tout tiers d'intercepter des messages qui ne lui sont pas destinés.
   * Tout compte non activé (statut `awaiting_password`) est automatiquement intercepté par un abonné d'événements noyau (`PasswordSetupRequiredSubscriber`) et redirigé vers l'étape de configuration du mot de passe, bloquant tout accès anticipé au portail.

