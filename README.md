# Evaluate

**Evaluate** est une application web développée en PHP permettant l’évaluation des enseignants par les étudiants. Elle facilite la collecte, la gestion et l’analyse des retours pour améliorer la qualité de l’enseignement.

## Sommaire

- [Fonctionnalités](#fonctionnalités)
- [Technologies utilisées](#technologies-utilisées)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
- [Structure du projet](#structure-du-projet)
- [Auteurs](#auteurs)
- [Licence](#licence)

## Fonctionnalités

- Authentification des utilisateurs (étudiants, enseignants, administrateurs)
- Saisie d’évaluations anonymes par les étudiants
- Visualisation des résultats par les enseignants et l’administration
- Génération de rapports statistiques (moyennes, graphiques, etc.)
- Gestion des questionnaires d’évaluation
- Interface d’administration pour la gestion des utilisateurs et des matières

## Technologies utilisées

- **Backend** : PHP (>=7.4)
- **Base de données** : MySQL/MariaDB
- **Frontend** : HTML5, CSS3, JavaScript (optionnel : Bootstrap)
- **Serveur web** : Apache/Nginx

## Installation

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/votre-utilisateur/Evaluate.git
   ```

2. **Déplacer les fichiers sur votre serveur web**
   - Placez le contenu du dossier `Evaluate` dans le répertoire racine de votre serveur (ex : `htdocs` ou `www`).

3. **Créer la base de données**
   - Importez le fichier `database.sql` (fourni dans le projet) dans votre serveur MySQL :
     ```bash
     mysql -u root -p < database.sql
     ```

4. **Configurer la connexion à la base de données**
   - Modifiez le fichier `config.php` avec vos identifiants MySQL :
     ```php
     // ...exemple...
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'evaluate');
     define('DB_USER', 'root');
     define('DB_PASS', 'votre_mot_de_passe');
     ```

5. **Vérifier les droits d’écriture**
   - Assurez-vous que le serveur web a les droits nécessaires sur les dossiers de logs et d’uploads.

## Utilisation

- Accédez à l’application via votre navigateur à l’adresse de votre serveur (ex : http://localhost/Evaluate).
- Connectez-vous avec un compte existant ou créez un nouvel utilisateur selon les droits attribués.
- Les étudiants peuvent remplir les questionnaires d’évaluation.
- Les enseignants et administrateurs peuvent consulter les résultats et générer des rapports.

## Structure du projet

```
Evaluate/
│
├── config.php           # Configuration de la base de données
├── index.php            # Page d’accueil
├── login.php            # Authentification
├── dashboard.php        # Tableau de bord
├── evaluation/          # Gestion des évaluations
├── admin/               # Interface d’administration
├── assets/              # Fichiers CSS, JS, images
├── database.sql         # Script de création de la base de données
└── README.md            # Ce fichier
```

## Auteurs

- [lmr_lumiere]


## Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus d’informations.