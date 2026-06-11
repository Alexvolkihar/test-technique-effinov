# FightClubPortal - Messagerie Chiffrée et Portail Membres

Bienvenue sur le portail secret du **Fight Club**. Ce projet Symfony implémente le cycle de vie d'inscription, de validation, de configuration de mot de passe et de messagerie privée chiffrée entre les membres actifs du club.

---

## 📋 Consignes du Test Technique
Les consignes originales de ce test sont disponibles au format Markdown :
👉 **[Consignes Originales](consignes/test-technique.md)**

---

## 🛠 Technologies Utilisées

* **PHP 8.4**
* **Symfony 8.1**
* **PostgreSQL 16**
* **Docker & Docker Compose**
* **Mailpit** (Serveur SMTP de test)
* **Doctrine ORM**
* **Twig & Symfony UX**

---

## 🚀 Installation et Démarrage

### 1. Cloner le projet
```bash
git clone https://github.com/AryyX/test-technique.git
cd test-technique-effinov
```

### 2. Configurer l'environnement
Créez votre fichier `.env` à partir de l'exemple :
```bash
cp .env.example .env
```

* **Note** : Vous n'avez pas besoin de .env pour executer l'application en local via docker tout est préconfiguré. Cf l'étape juste après

### 3. Démarrer les conteneurs Docker
Lancez les conteneurs en arrière-plan :
```bash
docker compose up -d --build
```
* **Note** : Le processus d'installation de Composer, l'attente de la base de données, la création de celle-ci et l'exécution des migrations Doctrine s'exécutent **automatiquement** lors du démarrage du conteneur `php`. Un simple `docker compose up -d --build` suffit donc pour tout initialiser d'un coup !

Une fois les conteneurs lancés, l'application est accessible à l'adresse suivante :
👉 **[http://localhost:8000](http://localhost:8000)**

Les conteneurs lancés incluent :
* **Web/PHP** : PHP 8.4 sur le port `localhost:8000`
* **Nginx** : Proxy web de l'application
* **Database** : PostgreSQL 16
* **Mailpit** : Serveur SMTP et interface web sur `localhost:8025`

### 4. Commandes manuelles (si besoin)
Si vous souhaitez réexécuter manuellement les dépendances ou les migrations :
```bash
# Installer les dépendances Composer
docker compose exec php composer install

# Créer la base de données (si non existante)
docker compose exec php bin/console doctrine:database:create --if-not-exists

# Exécuter les migrations de base de données
docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
```

---

### Flux d'inscription

1. Se rendre sur [http://localhost:8000](http://localhost:8000) ou [http://localhost:8000/inscription](http://localhost:8000/inscription)

2. Remplir le formulaire d'inscription

3. Un administrateur valide l'inscription via la commande CLI
Dans notre cas :

il liste les demandes d'inscription grâce à :

```bash
docker compose exec php bin/console app:list-registrations
```
puis il valide la première grâce à :

```bash
docker compose exec php bin/console app:review-registration <id> --decision=approve
```
Remplacer `<id>` par `1` s'il s'agit de la première inscription, et ainsi de suite.

Vous pouvez verifier l'id de l'inscription grâce à la commande de listing 

4. L'utilisateur reçoit un email avec un lien de validation
Intercepté par **Mailpit** ici : [http://localhost:8025](http://localhost:8025)

Après un clic sur le lien de validation :

5. L'utilisateur crée son mot de passe

6. L'utilisateur accède au portail

7. L'utilisateur accède à la messagerie

---

## 📧 Inspection des e-mails (Mailpit)
Toutes les notifications par e-mail (notamment les liens d'activation de compte) sont interceptées par **Mailpit** pour éviter tout envoi réel.
Vous pouvez consulter l'interface web de Mailpit à l'adresse suivante :
👉 [http://localhost:8025](http://localhost:8025)

---

## 🛡 Commandes d'Administration (Console Symfony)

L'approbation ou le rejet des demandes d'inscription s'effectue exclusivement depuis la ligne de commande via l'outil de console Symfony.

### Lister les demandes d'inscription
```bash
docker compose exec php bin/console app:list-registrations
```

### Approuver une candidature
```bash
docker compose exec php bin/console app:review-registration <id> --decision=approve
```
* **Effet** : La demande passe au statut `approved`, un compte membre est créé au statut `awaiting_password`, et un e-mail contenant le lien de validation à usage unique est envoyé via Mailpit.

### Rejeter une candidature
```bash
docker compose exec php bin/console app:review-registration <id> --decision=reject --reason="Motif de refus"
```
* **Effet** : La demande passe au statut `rejected` et le motif est enregistré dans le champ `reviewNote`.

---

## 🧪 Exécution des Tests

Le projet dispose d'une suite de tests complète (tests unitaires, fonctionnels et d'intégration de bout en bout).

### Exécuter la suite PHPUnit
```bash
docker compose exec php vendor/bin/phpunit
```

### Exécuter la suite Behat
```bash
docker compose exec php vendor/bin/behat
```

---

## 📂 Architecture Documentaire
Les spécifications et documentations techniques se trouvent dans le répertoire [specs/mabanza-alexis/](file:///Users/alexvolkihar/Documents/projetsPersos/test-technique-effinov/specs/mabanza-alexis/) :
* [plan.md](file:///Users/alexvolkihar/Documents/projetsPersos/test-technique-effinov/specs/mabanza-alexis/plan.md) : Plan d'implémentation
* [data-model.md](file:///Users/alexvolkihar/Documents/projetsPersos/test-technique-effinov/specs/mabanza-alexis/data-model.md) : Modèle de données & relations
* [research.md](file:///Users/alexvolkihar/Documents/projetsPersos/test-technique-effinov/specs/mabanza-alexis/research.md) : Décisions techniques
* [quickstart.md](file:///Users/alexvolkihar/Documents/projetsPersos/test-technique-effinov/specs/mabanza-alexis/quickstart.md) : Guide de démarrage rapide
* [contracts/](file:///Users/alexvolkihar/Documents/projetsPersos/test-technique-effinov/specs/mabanza-alexis/contracts/) : Spécification des parcours utilisateurs
