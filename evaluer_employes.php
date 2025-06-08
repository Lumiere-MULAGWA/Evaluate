<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_service') {
    header('Location: login.php');
    exit();
}
include('db.php');

$chef_id = $_SESSION['user_id'];

// Récupération des employés sélectionnés
$ids = $_POST['employes'] ?? [];
if (!is_array($ids) || empty($ids)) {
    echo "Aucun employé sélectionné.";
    exit();
}

// Sécuriser la requête avec des marqueurs
$in = str_repeat('?,', count($ids) - 1) . '?';
$sql = "SELECT * FROM utilisateurs WHERE id IN ($in) AND role = 'employe'";
$req = $pdo->prepare($sql);
$req->execute($ids);
$employes = $req->fetchAll();

// Enregistrement des évaluations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['note'])) {
    foreach ($_POST['note'] as $id_employe => $notes) {
        foreach ($notes as $critere => $note) {
            $commentaire = $_POST['commentaire'][$id_employe][$critere];
            $stmt = $pdo->prepare("INSERT INTO evaluations (id_employe, id_evaluateur, critere, note, commentaire, annee) 
                                   VALUES (?, ?, ?, ?, ?, YEAR(NOW()))");
            $stmt->execute([$id_employe, $chef_id, $critere, $note, $commentaire]);
        }
    }
    echo "<script>alert('Évaluations enregistrées avec succès !'); window.location='selection_employes.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Évaluation des employés sélectionnés</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        h2 { text-align: center; color: #0072ff; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; background: white; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background: #0072ff; color: white; }
        .danger { background: #ffc2c2; }
        button { padding: 10px 20px; background: #0072ff; color: white; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>

<h2>Évaluer les employés sélectionnés</h2>

<form method="POST">
<?php foreach ($employes as $emp): ?>
    <h3><?= htmlspecialchars($emp['nom']) ?></h3>
    <table>
        <tr>
            <th>Critère</th>
            <th>Note (0-100)</th>
            <th>Commentaire</th>
        </tr>
        <?php
        $criteres = ['Ponctualité', 'Compétence', 'Travail en équipe', 'Initiative'];
        foreach ($criteres as $critere): ?>
            <tr>
                <td><?= $critere ?></td>
                <td>
                    <input type="number" name="note[<?= $emp['id'] ?>][<?= $critere ?>]" min="0" max="100"
                        oninput="this.className = (this.value <= 50 ? 'danger' : '')">
                </td>
                <td>
                    <input type="text" name="commentaire[<?= $emp['id'] ?>][<?= $critere ?>]">
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endforeach; ?>
<!-- Transmettre aussi les employés sélectionnés pour la soumission POST -->
<?php foreach ($ids as $id): ?>
    <input type="hidden" name="employes[]" value="<?= $id ?>">
<?php endforeach; ?>

<button type="submit">Soumettre l’évaluation</button>
</form>

</body>
</html>
