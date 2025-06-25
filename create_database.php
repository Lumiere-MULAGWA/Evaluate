<?php
/**
 * Script de création de la base de données Evaluate
 * À exécuter AVANT install.php
 */

// Configuration de connexion (sans base de données spécifique)
$host = 'localhost';
$username = 'root';
$password = '';
$database_name = 'evaluation_personnel2';

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Création de la base de données - Evaluate</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; line-height: 1.6; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .header { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 30px; border-radius: 12px; text-align: center; margin-bottom: 30px; }
        .btn { background: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; margin: 10px 5px; font-size: 16px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .steps { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .step { margin: 10px 0; padding: 10px; border-left: 4px solid #007bff; background: white; }
    </style>
</head>
<body>";

echo "<div class='header'>
    <h1>🗄️ Création de la Base de Données</h1>
    <p>Evaluate - Système d'évaluation du personnel</p>
</div>";

try {
    // Connexion sans spécifier de base de données
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<div class='success'>✅ Connexion au serveur MySQL réussie</div>";
    
    // Vérifier si la base de données existe
    $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
    $stmt->execute([$database_name]);
    $database_exists = $stmt->fetch();
    
    if ($database_exists) {
        echo "<div class='warning'>⚠️ La base de données '$database_name' existe déjà</div>";
        
        // Afficher les options
        echo "<div class='info'>
            <h3>Options disponibles :</h3>
            <form method='post' style='margin: 20px 0;'>
                <input type='hidden' name='action' value='recreate'>
                <button type='submit' class='btn' onclick='return confirm(\"Êtes-vous sûr de vouloir recréer la base de données ? Toutes les données existantes seront perdues !\")'>
                    🔄 Recréer la base de données (ATTENTION: supprime toutes les données)
                </button>
            </form>
            
            <form method='post' style='margin: 20px 0;'>
                <input type='hidden' name='action' value='continue'>
                <button type='submit' class='btn btn-success'>
                    ➡️ Continuer avec la base existante
                </button>
            </form>
        </div>";
        
        // Traitement des actions
        if ($_POST['action'] ?? '' === 'recreate') {
            $pdo->exec("DROP DATABASE IF EXISTS $database_name");
            echo "<div class='success'>🗑️ Ancienne base de données supprimée</div>";
            
            $pdo->exec("CREATE DATABASE $database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "<div class='success'>✅ Nouvelle base de données '$database_name' créée</div>";
            
            showNextSteps();
        } elseif ($_POST['action'] ?? '' === 'continue') {
            echo "<div class='info'>➡️ Utilisation de la base de données existante</div>";
            showNextSteps();
        }
        
    } else {
        // Créer la base de données
        $sql = "CREATE DATABASE $database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $pdo->exec($sql);
        
        echo "<div class='success'>✅ Base de données '$database_name' créée avec succès</div>";
        
        // Créer un utilisateur dédié (optionnel)
        try {
            $user_sql = "CREATE USER IF NOT EXISTS 'evaluate_user'@'localhost' IDENTIFIED BY 'evaluate_pass_2024'";
            $pdo->exec($user_sql);
            
            $grant_sql = "GRANT ALL PRIVILEGES ON $database_name.* TO 'evaluate_user'@'localhost'";
            $pdo->exec($grant_sql);
            
            $pdo->exec("FLUSH PRIVILEGES");
            
            echo "<div class='success'>✅ Utilisateur MySQL 'evaluate_user' créé avec les privilèges appropriés</div>";
            echo "<div class='info'>
                <strong>Informations de l'utilisateur créé :</strong><br>
                👤 Utilisateur: evaluate_user<br>
                🔑 Mot de passe: evaluate_pass_2024<br>
                <em>(Vous pouvez modifier db.php pour utiliser cet utilisateur au lieu de root)</em>
            </div>";
            
        } catch (PDOException $e) {
            echo "<div class='warning'>⚠️ Impossible de créer l'utilisateur dédié (ce n'est pas grave): " . $e->getMessage() . "</div>";
        }
        
        showNextSteps();
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Erreur de connexion: " . $e->getMessage() . "</div>";
    
    echo "<div class='info'>
        <h3>🔧 Solutions possibles :</h3>
        <ul>
            <li><strong>Vérifiez que MySQL/MariaDB est démarré</strong>
                <pre>net start mysql</pre> (Windows) ou <pre>sudo systemctl start mysql</pre> (Linux)
            </li>
            <li><strong>Vérifiez les paramètres de connexion :</strong>
                <ul>
                    <li>Hôte: $host</li>
                    <li>Utilisateur: $username</li>
                    <li>Mot de passe: " . (empty($password) ? '(vide)' : '***') . "</li>
                </ul>
            </li>
            <li><strong>Testez la connexion MySQL :</strong>
                <pre>mysql -u $username" . (empty($password) ? '' : ' -p') . "</pre>
            </li>
        </ul>
    </div>";
}

function showNextSteps() {
    echo "<div class='steps'>
        <h3>🎯 Prochaines étapes :</h3>
        <div class='step'>
            <strong>1.</strong> Vérifiez le fichier <code>db.php</code> pour confirmer les paramètres de connexion
        </div>
        <div class='step'>
            <strong>2.</strong> Exécutez le script d'installation pour créer les tables et données
        </div>
        <div class='step'>
            <strong>3.</strong> Commencez à utiliser l'application
        </div>
    </div>";
    
    echo "<div style='text-align: center; margin: 30px 0;'>
        <a href='install.php' class='btn btn-success'>🚀 Exécuter l'installation des tables</a>
        <a href='db.php' class='btn'>🔧 Vérifier la configuration</a>
    </div>";
}

echo "
<div style='margin-top: 50px; padding: 20px; background: #f8f9fa; border-radius: 8px; font-size: 14px; color: #6c757d;'>
    <h4>📋 Configuration recommandée pour MySQL :</h4>
    <pre>
# Dans my.cnf ou my.ini
[mysql]
default-character-set = utf8mb4

[mysqld]
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
max_allowed_packet = 64M
innodb_file_format = Barracuda
innodb_file_per_table = 1
innodb_large_prefix = 1
    </pre>
</div>
</body>
</html>";
?>
