<?php
/**
 * Configuration de la base de données pour Evaluate
 * Système d'évaluation du personnel
 */

// Configuration de la base de données
$config = [
    'host' => 'localhost',
    'dbname' => 'evaluation_personnel2',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]
];

try {
    // Création de la connexion PDO
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    
    // Vérification de la connexion
    $pdo->query("SELECT 1");
    
} catch (PDOException $e) {
    // Log de l'erreur
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    
    // Message d'erreur pour l'utilisateur
    die("
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 100px auto; padding: 20px; background: #fee; border: 1px solid #fcc; border-radius: 8px;'>
        <h2 style='color: #c33;'>Erreur de connexion à la base de données</h2>
        <p>Impossible de se connecter à la base de données. Veuillez vérifier:</p>
        <ul>
            <li>Que le serveur MySQL est démarré</li>
            <li>Que la base de données 'evaluation_personnel' existe</li>
            <li>Les paramètres de connexion dans db.php</li>
        </ul>
        <p><strong>Erreur technique:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
    </div>
    ");
}

// Fonction utilitaire pour les requêtes sécurisées
function executeQuery($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Erreur SQL: " . $e->getMessage() . " - Requête: " . $sql);
        throw $e;
    }
}

// Fonction pour vérifier l'existence des tables
function checkDatabase($pdo) {
    $tables = ['utilisateurs', 'evaluations', 'services', 'departements'];
    $missing = [];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT 1 FROM $table LIMIT 1");
        } catch (PDOException $e) {
            $missing[] = $table;
        }
    }
    
    return $missing;
}

// Vérification des tables (optionnel, décommentez si nécessaire)
// $missingTables = checkDatabase($pdo);
// if (!empty($missingTables)) {
//     die("Tables manquantes: " . implode(', ', $missingTables));
// }
?>
