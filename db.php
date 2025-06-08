<?php
// db.php - Connexion à la base de données
$host = 'localhost';
$db = 'evaluation_personnel';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
} catch (PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}
?>
