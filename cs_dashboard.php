<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_service') {
    header('Location: login.php');
    exit();
}
include('db.php');

$chef_id = $_SESSION['user_id'];
$req = $pdo->prepare("SELECT u.* FROM utilisateurs u 
    JOIN utilisateurs chef ON u.id_service = chef.id_service 
    WHERE chef.id = ? AND u.role = 'employe'");
$req->execute([$chef_id]);
$employes = $req->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Sélectionner les employés à évaluer</title>
    <style>
        body { font-family: Arial; background: #f8f8f8; padding: 20px; }
        h2 { color: #0072ff; }
        form { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 8px #ccc; }
        label { display: block; margin: 8px 0; }
        input[type="checkbox"] { margin-right: 8px; }
        button { padding: 10px 20px; background: #0072ff; color: white; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>

<h2>Choisissez les employés à évaluer</h2>

<form method="POST" action="evaluer_employes.php">
    <?php foreach ($employes as $emp): ?>
        <label>
            <input type="checkbox" name="employes[]" value="<?= $emp['id'] ?>">
            <?= htmlspecialchars($emp['nom']) ?> (<?= htmlspecialchars($emp['email']) ?>)
        </label>
    <?php endforeach; ?>
    <br>
    <button type="submit">Lancer l’évaluation maintenant</button>
</form>

</body>
</html>
