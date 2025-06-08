<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_departement') {
    header('Location: login.php');
    exit();
}

include('db.php');

$id_cd = $_SESSION['user_id'];

// Récupérer les chefs de service du même département
$req = $pdo->prepare("SELECT id FROM utilisateurs WHERE id_departement = (SELECT id_departement FROM utilisateurs WHERE id = ?) AND role = 'chef_service'");
$req->execute([$id_cd]);
$chefs = $req->fetchAll(PDO::FETCH_COLUMN);

// Récupérer les évaluations faites par les chefs de service
$evaluations = [];
if (!empty($chefs)) {
    $in  = str_repeat('?,', count($chefs) - 1) . '?';
    $sql = "SELECT e.*, u.nom AS employe_nom FROM evaluations e JOIN utilisateurs u ON e.id_employe = u.id WHERE id_evaluateur IN ($in) ORDER BY e.id_employe";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($chefs);
    $evaluations = $stmt->fetchAll();
}

// Grouper les évaluations par employé
$groupes = [];
foreach ($evaluations as $e) {
    $groupes[$e['id_employe']]['nom'] = $e['employe_nom'];
    $groupes[$e['id_employe']]['evaluations'][] = $e;
}

// Soumettre au DRH
if (isset($_POST['soumettre'])) {
    // Simuler envoi mail ou stockage interne
    $stmt = $pdo->prepare("INSERT INTO soumissions (id_cd, annee) VALUES (?, YEAR(NOW()))");
    $stmt->execute([$id_cd]);
    echo "<script>alert('Soumission envoyée au DRH !');</script>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Chef de Département - Evaluations</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        h2 { text-align: center; color: #0072ff; }
        table { width: 100%; border-collapse: collapse; background: white; margin-bottom: 30px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background: #0072ff; color: white; }
        .danger { background-color: #ffdddd; }
        button { background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px; }
        button:hover { background: #218838; }
    </style>
</head>
<body>
    <h2>Tableau de bord - Chef de Département</h2>

    <form method="post">
        <?php if (empty($groupes)): ?>
            <p>Aucune évaluation reçue pour l'instant.</p>
        <?php else: ?>
            <?php foreach ($groupes as $id_emp => $data): ?>
                <h3><?= htmlspecialchars($data['nom']) ?></h3>
                <table>
                    <tr>
                        <th>Critère</th>
                        <th>Note</th>
                        <th>Commentaire</th>
                    </tr>
                    <?php foreach ($data['evaluations'] as $eval): ?>
                        <tr class="<?= $eval['note'] <= 50 ? 'danger' : '' ?>">
                            <td><?= htmlspecialchars($eval['critere']) ?></td>
                            <td><?= htmlspecialchars($eval['note']) ?>%</td>
                            <td><?= htmlspecialchars($eval['commentaire']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endforeach; ?>
            <button type="submit" name="soumettre">Soumettre toutes les évaluations au DRH</button>
        <?php endif; ?>
    </form>
</body>
</html>
