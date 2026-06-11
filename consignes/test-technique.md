# Test Technique : système d'inscription à un site web et du login après validation

## Objectif général

Développer une application full-stack permettant de **gérer un processus d'inscription, de validation et un login**.

Le test vise à démontrer la maîtrise :

* de la **programmation orientée objet avancée** (interfaces, abstractions, factory pattern, services…),
* de l’écosystème **Symfony, Symfony UX (Twig Components et Live Components) et Bootstrap**,
* de **Docker** pour l’environnement d'exécution.
* l'écriture de tests unitaires et fonctionnels avec PHPUnit et Behat.

---

## Contexte

FightClubPortal est une application web secrete permettant aux membres de s'échanger des messages en toutes discretion.
Les membres doivent s'inscrire sur une interface dédier, en remplissant un formulaire devant contenir :
* Nom
* Prenom
* Adresse
* Date de naissance
* Numéro sécurité social
* Pseudo de combattant
* Numéro d'accréditation de combattant délivré par le CERFA 666.
* Pokemon Starter choisi (edition bleu ou rouge)

Après validation par un administrateur (pour les besoins, pas d'interface d'administration, elle doit être faite via
une commande CLI utilisant Symfony Console). La fiche utilisateur est créé avec son identifiant interne. Un email est
envoyé à l'utilisateur avec un lien de validation.

Au click de ce lien, l'utilisateur est alors redirigé vers une page de création de son mot de passe. Tant qu'il n'a
pas saisi son mot de passe, il ne peut aller ailleurs. Une fois terminé, il accède au portail ou la première règle 
est de ne rien dire.

## Livrable

* L'application Symfony sans les vendor mais avec le composer.lock
* Le docker compose et les fichiers nécessaire à la construction des éventuelles images.
* Les tests unitaires et fonctionnels.
* En Markdown ou en PDF:
  * Un document d'architecture
  * Un schema relationnel
  * Un diagramme de classes (UML)
  * Un diagramme de flux (UML)
* Un fichier README.md permettant de lancer l'application
