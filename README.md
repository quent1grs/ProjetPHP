# Projet PHP - Gestion de Comptes, Articles et Factures

## Description

Ce projet est une application web développée en PHP avec une base de données MySQL. Elle permet aux utilisateurs de gérer leur compte, publier et acheter des articles, et consulter leurs factures.

## Prérequis

### Environnement de développement

- **Serveur local** : Pour exécuter ce projet, vous devez utiliser un environnement serveur local comme XAMPP ou WAMP qui inclut Apache, PHP et MySQL.
- **Navigateur web compatible** : Assurez-vous d'utiliser un navigateur moderne pour une meilleure expérience.

## Installation et Configuration

### Étape 1 : Installer un serveur local

- **Télécharger XAMPP** : [Lien de téléchargement XAMPP](https://www.apachefriends.org/download.html)
- **Télécharger WAMP** : [Lien de téléchargement WAMP](http://www.wampserver.com/)

Installez et démarrez Apache et MySQL à partir de l'interface de XAMPP ou WAMP.

### Étape 2 : Cloner ou copier le projet

- **Pour XAMPP** : Copiez le dossier du projet dans `C:\xampp\htdocs\`
- **Pour WAMP** : Copiez le dossier du projet dans `C:\wamp64\www\`

### Étape 3 : Importer la base de données

1. Ouvrez phpMyAdmin (ex: [http://localhost/phpmyadmin](http://localhost/phpmyadmin))
2. Créez une nouvelle base de données nommée `projetphp`
3. Importez le fichier SQL fourni `database.sql`

### Étape 4 : Configurer la connexion à la base de données

Modifiez le fichier `db.php` avec vos identifiants MySQL :

```php
\$pdo = new PDO('mysql\:host=localhost;dbname=projetphp;charset=utf8', 'root', '');
Accéder au projet
Ouvrez votre navigateur et accédez à l'URL suivante :

Copier
http://localhost/nom_du_dossier_du_projet/
Fonctionnalités
Gestion des utilisateurs : Profil, modification et suppression.
Publication et consultation des articles : Ajout et visualisation des articles.
Achat et affichage des articles achetés : Processus d'achat et historique.
Consultation des factures : Détails des factures.
Sécurité basique : Gestion de sessions et échappement des données.