<?php
/**
 * Script d'installation de la base de données Evaluate
 * Exécutez ce fichier une seule fois pour créer les tables et données de base
 */

require_once 'db.php';

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Installation - Evaluate</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; line-height: 1.6; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; border-radius: 12px; text-align: center; margin-bottom: 30px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 8px; overflow-x: auto; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>";

echo "<div class='header'>
    <h1>🚀 Installation Evaluate</h1>
    <p>Système d'évaluation du personnel - Configuration automatique</p>
</div>";

try {
    // 1. Création de la table utilisateurs
    echo "<h2>📋 Création des tables</h2>";
    
    $sql_users = "CREATE TABLE IF NOT EXISTS utilisateurs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        email VARCHAR(150) UNIQUE NOT NULL,
        mot_de_passe VARCHAR(255) NOT NULL,
        role ENUM('drh', 'chef_departement', 'chef_service', 'employe') NOT NULL DEFAULT 'employe',
        id_service INT NULL,
        id_departement INT NULL,
        telephone VARCHAR(20) NULL,
        date_embauche DATE NULL,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        actif BOOLEAN DEFAULT TRUE,
        derniere_connexion TIMESTAMP NULL,
        INDEX idx_role (role),
        INDEX idx_service (id_service),
        INDEX idx_departement (id_departement),
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql_users);
    echo "<div class='success'>✅ Table 'utilisateurs' créée avec succès</div>";
    
    // 2. Création de la table départements
    $sql_departements = "CREATE TABLE IF NOT EXISTS departements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL UNIQUE,
        description TEXT NULL,
        chef_departement_id INT NULL,
        budget DECIMAL(15,2) NULL,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        actif BOOLEAN DEFAULT TRUE,
        INDEX idx_chef (chef_departement_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql_departements);
    echo "<div class='success'>✅ Table 'departements' créée avec succès</div>";
    
    // 3. Création de la table services
    $sql_services = "CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        description TEXT NULL,
        id_departement INT NOT NULL,
        chef_service_id INT NULL,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        actif BOOLEAN DEFAULT TRUE,
        INDEX idx_departement (id_departement),
        INDEX idx_chef (chef_service_id),
        FOREIGN KEY (id_departement) REFERENCES departements(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql_services);
    echo "<div class='success'>✅ Table 'services' créée avec succès</div>";
    
    // 4. Création de la table évaluations
    $sql_evaluations = "CREATE TABLE IF NOT EXISTS evaluations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_employe INT NOT NULL,
        id_evaluateur INT NOT NULL,
        critere VARCHAR(100) NOT NULL,
        note INT NOT NULL CHECK (note >= 0 AND note <= 100),
        commentaire TEXT NULL,
        periode_debut DATE NOT NULL,
        periode_fin DATE NOT NULL,
        statut ENUM('brouillon', 'finalise', 'valide') DEFAULT 'brouillon',
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_employe (id_employe),
        INDEX idx_evaluateur (id_evaluateur),
        INDEX idx_periode (periode_debut, periode_fin),
        INDEX idx_critere (critere),
        INDEX idx_statut (statut),
        FOREIGN KEY (id_employe) REFERENCES utilisateurs(id) ON DELETE CASCADE,
        FOREIGN KEY (id_evaluateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
        UNIQUE KEY unique_evaluation (id_employe, id_evaluateur, critere, periode_debut, periode_fin)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql_evaluations);
    echo "<div class='success'>✅ Table 'evaluations' créée avec succès</div>";
    
    // 5. Création de la table objectifs
    $sql_objectifs = "CREATE TABLE IF NOT EXISTS objectifs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_employe INT NOT NULL,
        id_createur INT NOT NULL,
        titre VARCHAR(200) NOT NULL,
        description TEXT NOT NULL,
        date_echeance DATE NOT NULL,
        priorite ENUM('basse', 'moyenne', 'haute', 'critique') DEFAULT 'moyenne',
        statut ENUM('nouveau', 'en_cours', 'termine', 'reporte', 'annule') DEFAULT 'nouveau',
        progres INT DEFAULT 0 CHECK (progres >= 0 AND progres <= 100),
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_employe (id_employe),
        INDEX idx_createur (id_createur),
        INDEX idx_echeance (date_echeance),
        INDEX idx_statut (statut),
        FOREIGN KEY (id_employe) REFERENCES utilisateurs(id) ON DELETE CASCADE,
        FOREIGN KEY (id_createur) REFERENCES utilisateurs(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql_objectifs);
    echo "<div class='success'>✅ Table 'objectifs' créée avec succès</div>";
    
    // 6. Ajout des contraintes de clés étrangères
    echo "<h2>🔗 Configuration des relations</h2>";
    
    // Contraintes pour utilisateurs
    try {
        $pdo->exec("ALTER TABLE utilisateurs 
                   ADD CONSTRAINT fk_user_service FOREIGN KEY (id_service) REFERENCES services(id) ON DELETE SET NULL,
                   ADD CONSTRAINT fk_user_departement FOREIGN KEY (id_departement) REFERENCES departements(id) ON DELETE SET NULL");
        echo "<div class='success'>✅ Contraintes utilisateurs ajoutées</div>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key') === false) {
            echo "<div class='info'>ℹ️ Contraintes utilisateurs déjà existantes</div>";
        }
    }
    
    // Contraintes pour départements et services
    try {
        $pdo->exec("ALTER TABLE departements 
                   ADD CONSTRAINT fk_dept_chef FOREIGN KEY (chef_departement_id) REFERENCES utilisateurs(id) ON DELETE SET NULL");
        echo "<div class='success'>✅ Contraintes départements ajoutées</div>";
    } catch (PDOException $e) {
        echo "<div class='info'>ℹ️ Contraintes départements déjà existantes</div>";
    }
    
    try {
        $pdo->exec("ALTER TABLE services 
                   ADD CONSTRAINT fk_service_chef FOREIGN KEY (chef_service_id) REFERENCES utilisateurs(id) ON DELETE SET NULL");
        echo "<div class='success'>✅ Contraintes services ajoutées</div>";
    } catch (PDOException $e) {
        echo "<div class='info'>ℹ️ Contraintes services déjà existantes</div>";
    }
    
    // 7. Insertion des données de base
    echo "<h2>📊 Insertion des données de base</h2>";
    
    // Créer des départements par défaut
    $departements = [
        ['Ressources Humaines', 'Gestion du personnel et des compétences'],
        ['Informatique', 'Développement et maintenance des systèmes'],
        ['Commercial', 'Ventes et relation client'],
        ['Marketing', 'Communication et promotion'],
        ['Finance', 'Comptabilité et gestion financière']
    ];
    
    foreach ($departements as $dept) {
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO departements (nom, description) VALUES (?, ?)");
            $stmt->execute($dept);
        } catch (PDOException $e) {
            // Ignore les doublons
        }
    }
    echo "<div class='success'>✅ Départements par défaut créés</div>";
    
    // Créer des services par défaut
    $services = [
        ['Recrutement', 'Recrutement et intégration', 1],
        ['Formation', 'Formation et développement', 1],
        ['Développement Web', 'Applications web et sites', 2],
        ['Infrastructure', 'Serveurs et réseaux', 2],
        ['Vente B2B', 'Vente aux entreprises', 3],
        ['Vente B2C', 'Vente aux particuliers', 3],
        ['Communication', 'Communication externe', 4],
        ['Digital', 'Marketing digital', 4],
        ['Comptabilité', 'Comptabilité générale', 5],
        ['Contrôle de gestion', 'Analyse financière', 5]
    ];
    
    foreach ($services as $service) {
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO services (nom, description, id_departement) VALUES (?, ?, ?)");
            $stmt->execute($service);
        } catch (PDOException $e) {
            // Ignore les doublons
        }
    }
    echo "<div class='success'>✅ Services par défaut créés</div>";
    
    // 8. Création du compte administrateur
    echo "<h2>👤 Création du compte administrateur</h2>";
    
    $admin_password = 'Admin123!';
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO utilisateurs (nom, prenom, email, mot_de_passe, role, id_departement) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Administrateur', 'Système', 'admin@evaluate.local', $hashed_password, 'drh', 1]);
        
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✅ Compte administrateur créé avec succès</div>";
            echo "<div class='info'>
                <strong>Identifiants de connexion :</strong><br>
                📧 Email: admin@evaluate.local<br>
                🔑 Mot de passe: {$admin_password}
            </div>";
        } else {
            echo "<div class='info'>ℹ️ Compte administrateur déjà existant</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Erreur lors de la création du compte administrateur: " . $e->getMessage() . "</div>";
    }
    
    // 9. Création d'utilisateurs de test
    echo "<h2>🧪 Création d'utilisateurs de test</h2>";
     $test_password = password_hash('Test123!', PASSWORD_DEFAULT);
    $test_users = [
        ['name'=>'Martin', 'prename'=>'Pierre', 'email'=>'chef.dev@test.local', 'password'=>$test_password, 'role'=> 'chef_service', 'id_service'=> 3, 'id_departement'=> 2],
        ['name'=>'Dubois', 'prename'=>'Marie', 'email'=>'chef.commercial@test.local', 'password'=>$test_password, 'role'=> 'chef_service', 'id_service'=> 5, 'id_departement'=> 3],
        ['name'=>'Lefebvre', 'prename'=>'Jean', 'email'=>'chef.marketing@test.local', 'password'=>$test_password, 'role'=> 'chef_departement', 'id_service'=> 8, 'id_departement'=> 4],
        ['name'=>'Moreau', 'prename'=>'Sophie', 'email'=>'employe1@test.local', 'password'=>$test_password, 'role'=> 'employe', 'id_service'=> 3, 'id_departement'=> 2],
        ['name'=>'Garcia', 'prename'=>'Luis', 'email'=>'employe2@test.local', 'password'=>$test_password, 'role'=> 'employe', 'id_service'=> 5, 'id_departement'=> 3],
        ['name'=>'Bernard', 'prename'=>'Alice', 'email'=>'employe3@test.local', 'password'=>$test_password, 'role'=> 'employe', 'id_service'=> 7, 'id_departement'=> 4]
    ];
    
   
    
    foreach ($test_users as $user) {
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO utilisateurs (nom, prenom, email, mot_de_passe, role, id_service, id_departement) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user['name'],
                $user['prename'], 
                $user['email'], 
                $user['password'],
                $user['role'], 
                $user['id_service'], 
                $user['id_departement']
            ]);
            
            if ($stmt->rowCount() > 0) {
                echo "<div class='success'>✅ Utilisateur de test {$user['prename']} {$user['name']} ({$user['role']}) créé (mot de passe: Test123!)</div>";
            } else {
                echo "<div class='info'>ℹ️ Utilisateur {$user['prename']} {$user['name']} déjà existant</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Erreur pour {$user['prename']} {$user['name']}: " . $e->getMessage() . "</div>";
        }
    }
    
    
    echo "<h2>🎉 Installation terminée avec succès !</h2>";
    echo "<div class='success'>
        <h3>Prochaines étapes :</h3>
        <ol>
            <li>🔒 Connectez-vous avec le compte administrateur</li>
            <li>👥 Configurez les utilisateurs et leurs rôles</li>
            <li>📊 Commencez à utiliser le système d'évaluation</li>
            <li>🗑️ Supprimez ce fichier install.php pour des raisons de sécurité</li>
        </ol>
    </div>";
    
    echo "<div style='text-align: center; margin: 30px 0;'>
        <a href='login.php' class='btn'>🚀 Accéder à l'application</a>
    </div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Erreur lors de l'installation: " . $e->getMessage() . "</div>";
    echo "<div class='info'>
        <h3>Solutions possibles :</h3>
        <ul>
            <li>Vérifiez que MySQL est démarré</li>
            <li>Vérifiez les paramètres de connexion dans db.php</li>
            <li>Assurez-vous que l'utilisateur MySQL a les droits CREATE TABLE</li>
        </ul>
    </div>";
}

echo "
<div style='margin-top: 50px; padding: 20px; background: #f8f9fa; border-radius: 8px; font-size: 14px; color: #6c757d;'>
    <h4>📋 Structure des tables créées :</h4>
    <ul>
        <li><strong>utilisateurs</strong> - Gestion des comptes et rôles</li>
        <li><strong>departements</strong> - Organisation par département</li>
        <li><strong>services</strong> - Sous-divisions des départements</li>
        <li><strong>evaluations</strong> - Stockage des évaluations</li>
        <li><strong>objectifs</strong> - Gestion des objectifs individuels</li>
    </ul>
</div>
</body>
</html>";
?>
