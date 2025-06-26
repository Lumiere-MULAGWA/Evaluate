# Evaluate - Système d'Évaluation du Personnel

**Evaluate** est une application web moderne développée en PHP permettant l'évaluation du personnel par les managers. Elle facilite la collecte, la gestion et l'analyse des évaluations pour améliorer les performances organisationnelles.

## 🌟 Fonctionnalités

### 🔐 Système d'Authentification
- Connexion sécurisée avec validation des données
- Gestion des sessions et déconnexion automatique
- Interface de connexion moderne et responsive

### 👥 Gestion des Rôles
- **DRH** : Vue d'ensemble complète, gestion des utilisateurs
- **Chef de Département** : Supervision des services
- **Chef de Service** : Évaluation directe des employés
- **Employé** : Consultation des évaluations reçues

### 📊 Évaluations
- **Critères multiples** : Ponctualité, Compétence, Travail en équipe, Initiative, Qualité du travail, Adaptation
- **Notation sur 100** avec indicateurs visuels
- **Commentaires détaillés** pour chaque critère
- **Suivi de progression** en temps réel
- **Sauvegarde automatique** (optionnel)

### 📈 Tableau de Bord
- **Statistiques en temps réel** avec animations
- **Graphiques interactifs** pour visualiser les données
- **Indicateurs de performance** par service/département
- **Historique des évaluations**

### 🎨 Interface Moderne
- **Design responsive** adaptable à tous les écrans
- **Animations fluides** et transitions CSS
- **Thème moderne** avec variables CSS customisables
- **Notifications en temps réel**
- **Barre de progression** pour les évaluations

## 🛠 Technologies Utilisées

### Backend
- **PHP 7.4+** - Langage principal
- **MySQL/MariaDB** - Base de données
- **PDO** - Accès sécurisé aux données
- **Sessions PHP** - Gestion de l'authentification

### Frontend
- **HTML5** - Structure sémantique
- **CSS3** avec variables custom - Styles modernes
- **JavaScript ES6+** - Interactions dynamiques
- **Font Awesome 6** - Icônes vectorielles
- **CSS Grid & Flexbox** - Layout responsive

### Sécurité
- **Hashage des mots de passe** avec `password_hash()`
- **Requêtes préparées** PDO
- **Validation des données** côté client et serveur
- **Protection CSRF** (à implémenter)
- **Échappement des sorties** avec `htmlspecialchars()`

## 📁 Structure du Projet

```
Evaluate/
├── 📄 index.php                 # Page d'accueil avec redirection par rôle
├── 🔐 login.php                 # Authentification moderne
├── 🚪 logout.php                # Déconnexion sécurisée
├── 🗄️ db.php                    # Configuration base de données
├── 👥 selection_employes.php    # Interface de sélection des employés
├── 📝 evaluer_employes.php      # Formulaire d'évaluation complet
├── 📊 drh_dashboard.php         # Tableau de bord DRH (à créer)
├── 🏢 cd_dashboard.php          # Tableau de bord Chef Département (à créer)
├── 👨‍💼 cs_dashboard.php          # Tableau de bord Chef Service (à créer)
├── 🔧 add_user.php              # Gestion des utilisateurs (à créer)
└── assets/
    ├── css/
    │   └── 🎨 style.css         # Feuille de style complète et moderne
    └── js/
        └── ⚡ app.js            # JavaScript modulaire et fonctionnel
```

## 🚀 Installation

### Prérequis
- **Serveur web** : Apache/Nginx
- **PHP** : Version 7.4 ou supérieure
- **Base de données** : MySQL 5.7+ ou MariaDB 10.2+
- **Extensions PHP** : PDO, PDO_MySQL

### Étapes d'installation

1. **Clonage du projet**
   ```bash
   git clone https://github.com/mon-nom/Evaluate.git
   cd Evaluate
   ```

2. **Configuration du serveur web**
   - Placez les fichiers dans le répertoire racine de votre serveur web
   - Assurez-vous que le serveur peut lire/écrire dans le dossier

3. **Configuration de la base de données**
   
   Créez la base de données et les tables :
   ```sql
   CREATE DATABASE evaluation_personnel;
   USE evaluation_personnel;
   
   -- Table des utilisateurs
   CREATE TABLE utilisateurs (
       id INT AUTO_INCREMENT PRIMARY KEY,
       nom VARCHAR(100) NOT NULL,
       email VARCHAR(150) UNIQUE NOT NULL,
       mot_de_passe VARCHAR(255) NOT NULL,
       role ENUM('drh', 'chef_departement', 'chef_service', 'employe') NOT NULL,
       id_service INT NULL,
       id_departement INT NULL,
       date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       actif BOOLEAN DEFAULT TRUE
   );
   
   -- Table des évaluations
   CREATE TABLE evaluations (
       id INT AUTO_INCREMENT PRIMARY KEY,
       id_employe INT NOT NULL,
       id_evaluateur INT NOT NULL,
       critere VARCHAR(100) NOT NULL,
       note INT NOT NULL CHECK (note >= 0 AND note <= 100),
       commentaire TEXT,
       date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (id_employe) REFERENCES utilisateurs(id),
       FOREIGN KEY (id_evaluateur) REFERENCES utilisateurs(id),
       INDEX idx_employe_annee (id_employe, date_creation),
       INDEX idx_evaluateur (id_evaluateur)
   );
   
   -- Table des services
   CREATE TABLE services (
       id INT AUTO_INCREMENT PRIMARY KEY,
       nom VARCHAR(100) NOT NULL,
       id_departement INT,
       chef_service_id INT,
       INDEX idx_departement (id_departement)
   );
   
   -- Table des départements
   CREATE TABLE departements (
       id INT AUTO_INCREMENT PRIMARY KEY,
       nom VARCHAR(100) NOT NULL,
       chef_departement_id INT
   );
   ```

4. **Configuration de la connexion**
   
   Modifiez le fichier `db.php` :
   ```php
   <?php
   $host = 'localhost';          // Hôte de la base de données
   $db = 'evaluation_personnel'; // Nom de la base de données
   $user = 'root';              // Nom d'utilisateur
   $pass = 'votre_mot_de_passe'; // Mot de passe
   
   try {
       $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
           PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
           PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
           PDO::ATTR_EMULATE_PREPARES => false
       ]);
   } catch (PDOException $e) {
       die("Erreur de connexion : " . $e->getMessage());
   }
   ?>
   ```

5. **Création d'un compte administrateur**
   ```php
   // Script à exécuter une seule fois pour créer un compte DRH
   $password = password_hash('admin123', PASSWORD_DEFAULT);
   $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
   $stmt->execute(['Administrateur', 'admin@evaluate.com', $password, 'drh']);
   ```

## 📱 Utilisation

### Connexion
1. Accédez à l'application via `http://votre-serveur/Evaluate/`
2. Connectez-vous avec vos identifiants
3. Vous serez redirigé vers votre tableau de bord selon votre rôle

### Pour les Chefs de Service
1. **Sélection des employés** : Choisissez les employés à évaluer
2. **Évaluation** : Remplissez les critères avec notes et commentaires
3. **Sauvegarde** : Les évaluations sont enregistrées en base de données

### Pour les DRH et Chefs de Département
1. **Vue d'ensemble** : Consultez les statistiques globales
2. **Rapports** : Générez des rapports par service/département
3. **Gestion** : Administrez les utilisateurs et les données

## 🎨 Personnalisation

### Thèmes et Couleurs
Le système utilise des variables CSS pour faciliter la personnalisation :

```css
:root {
    --primary-color: #2563eb;      /* Couleur principale */
    --success-color: #10b981;      /* Couleur de succès */
    --danger-color: #ef4444;       /* Couleur de danger */
    --warning-color: #f59e0b;      /* Couleur d'avertissement */
    /* ... autres variables */
}
```

### Critères d'Évaluation
Modifiez le tableau `$criteres` dans `evaluer_employes.php` :

```php
$criteres = [
    'Ponctualité' => 'Respect des horaires et des échéances',
    'Compétence' => 'Maîtrise technique et professionnelle',
    'Votre_Critere' => 'Description de votre critère',
    // Ajoutez vos critères personnalisés
];
```

## 🔧 Fonctionnalités Avancées

### Notifications en Temps Réel
```javascript
// Utilisation du système de notifications
Notifications.success('Évaluation enregistrée avec succès !');
Notifications.error('Erreur lors de la sauvegarde');
Notifications.warning('Attention : données incomplètes');
Notifications.info('Information importante');
```

### Validation de Formulaires
```javascript
// Validation automatique avec data-attributes
<input type="email" data-validate="required|email" />
<input type="text" data-validate="required|minLength:3|maxLength:50" />
<input type="number" data-validate="required|range:0:100" />
```

### Animations et Transitions
- **Animations CSS** fluides avec `@keyframes`
- **Intersection Observer** pour les animations au scroll
- **Transitions** sur les interactions utilisateur

## 📊 Métriques et Performance

### Optimisations Incluses
- **Lazy loading** des images et contenus
- **Minification** CSS/JS (recommandée en production)
- **Compression GZIP** (à configurer sur le serveur)
- **Cache des requêtes** SQL répétitives

### Sécurité
- ✅ **Validation des entrées** côté client et serveur
- ✅ **Requêtes préparées** PDO
- ✅ **Hashage sécurisé** des mots de passe
- ✅ **Échappement des sorties** HTML
- ⚠️ **Protection CSRF** (à implémenter)
- ⚠️ **Rate limiting** (à implémenter)

## 🐛 Débogage

### Logs d'Erreurs
Activez les logs PHP pour le débogage :
```php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
```

### Console JavaScript
Le système affiche des messages de debug dans la console :
```javascript
console.log('📊 Evaluate - Système d\'évaluation du personnel initialisé avec succès!');
```

## 🚧 Développements Futurs

### Fonctionnalités Prévues
- [ ] **API REST** pour intégrations externes
- [ ] **Exportation PDF** des rapports
- [ ] **Notifications email** automatiques
- [ ] **Dashboard analytics** avancé avec Chart.js
- [ ] **Système de commentaires** collaboratifs
- [ ] **Historique des modifications**
- [ ] **Import/Export** des données
- [ ] **Authentification à double facteur**

### Améliorations Techniques
- [ ] **Tests unitaires** PHP et JavaScript
- [ ] **CI/CD Pipeline** avec GitHub Actions
- [ ] **Docker** pour le déploiement
- [ ] **PWA** (Progressive Web App)
- [ ] **Mode hors-ligne** avec Service Workers

## 👨‍💻 Contribution

### Comment Contribuer
1. **Fork** le projet
2. **Créez** une branche pour votre fonctionnalité
3. **Committez** vos changements
4. **Pushez** vers la branche
5. **Ouvrez** une Pull Request

### Standards de Code
- **PSR-12** pour le PHP
- **ES6+** pour le JavaScript
- **BEM** pour le CSS
- **Commentaires** en français
- **Variables** en camelCase (JS) et snake_case (PHP)

## 📄 Licence

Ce projet est sous licence **MIT**. Voir le fichier `LICENSE` pour plus d'informations.

## 👤 Auteur

**lmr_lumiere** - Développeur principal

## 🙏 Remerciements

- **Font Awesome** pour les icônes
- **PHP Community** pour les bonnes pratiques
- **MDN Web Docs** pour les références CSS/JS

---

## 📞 Support

Pour toute question ou problème :

- 📧 **Email** : support@evaluate.com
- 📚 **Documentation** : [Wiki du projet](link-to-wiki)
- 🐛 **Bugs** : [Issues GitHub](link-to-issues)
- 💬 **Discussions** : [Forum communautaire](link-to-forum)

---

*Dernière mise à jour : Juin 2025 - Version 2.0*
