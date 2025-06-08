<?php
session_start();
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'drh') {
//     header('Location: login.php');
//     exit();
// }

require 'db.php';

$errors = [];
$success = '';

$stmt_services = $pdo->query("SELECT id, nom FROM services ORDER BY nom");
$services = $stmt_services->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $motdepasse = $_POST['motdepasse'] ?? '';
    $role = $_POST['role'] ?? '';
    $id_service = $_POST['id_service'] ?? null;

    if (!$nom || !$email || !$motdepasse || !$role || !$id_service) {
        $errors[] = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    } elseif (!in_array($role, ['drh', 'chef_departement', 'chef_service' ,'employe'])) {
        $errors[] = "Rôle invalide.";
    } else {
        // Vérifier que le service existe bien
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM services WHERE id = ?");
        $stmt->execute([$id_service]);
        if ($stmt->fetchColumn() == 0) {
            $errors[] = "Service sélectionné invalide.";
        }
    }

    if (!$errors) {
        $hash = password_hash($motdepasse, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role, id_service) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([$nom, $email, $hash, $role, $id_service]);

        if ($result) {
            $success = "Utilisateur ajouté avec succès.";
        } else {
            $errors[] = "Erreur lors de l'ajout en base.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Ajouter un utilisateur</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; }
        input, select { width: 100%; padding: 8px; margin: 6px 0; }
        button { background-color: #3498db; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #2980b9; }
        .error { color: red; }
        .success { color: green; }
        label { font-weight: bold; }
    </style>
</head>
<body>

<h2>Ajouter un utilisateur</h2>

<?php if ($errors): ?>
    <div class="error">
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="POST" action="">
    <label for="nom">Nom complet *</label>
    <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" />

    <label for="email">Email *</label>
    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />

    <label for="motdepasse">Mot de passe *</label>
    <input type="password" id="motdepasse" name="motdepasse" required />

    <label for="role">Rôle *</label>
    <select id="role" name="role" required>
        <option value="">-- Choisissez un rôle --</option>
        <option value="employe" <?= (($_POST['role'] ?? '') === 'employe') ? 'selected' : '' ?>>Employe</option>
        <option value="drh" <?= (($_POST['role'] ?? '') === 'drh') ? 'selected' : '' ?>>DRH</option>
        <option value="chef_departement" <?= (($_POST['role'] ?? '') === 'chef_departement') ? 'selected' : '' ?>>Chef de département</option>
        <option value="chef_service" <?= (($_POST['role'] ?? '') === 'chef_service') ? 'selected' : '' ?>>Chef de service</option>
    </select>

    <label for="id_service">Service *</label>
    <select id="id_service" name="id_service" required>
        <option value="">-- Choisissez un service --</option>
        <?php foreach ($services as $service): ?>
            <option value="<?= $service['id'] ?>" <?= (($_POST['id_service'] ?? '') == $service['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($service['nom']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Ajouter</button>
</form>

</body>
</html>
