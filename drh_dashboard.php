<?php
require('db.php');


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Récupérer toutes les évaluations
$requete = $pdo->prepare("SELECT u.nom AS employe, d.nom AS departement, e.critere, e.note, e.commentaire, e.periode_fin
                         FROM evaluations e
                         JOIN utilisateurs u ON e.id_employe = u.id
                         JOIN departements d ON u.id_departement = d.id
                         ORDER BY e.periode_fin DESC");
$requete->execute();
$evaluations = $requete->fetchAll();

// Statistiques pour le dashboard
$stats = $pdo->query("SELECT 
    COUNT(*) as total_evaluations,
    AVG(note) as moyenne_generale,
    COUNT(CASE WHEN note <= 50 THEN 1 END) as evaluations_critiques,
    COUNT(CASE WHEN note >= 80 THEN 1 END) as evaluations_excellentes
    FROM evaluations")->fetch();

// Export Excel si demandé
if (isset($_POST['export_excel'])) {
    $filename = 'evaluations_' . date('Y-m-d') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $output = fopen('php://output', 'w');
    
    // En-têtes
    fputcsv($output, ['Employé', 'Département', 'Critère', 'Note', 'Commentaire', 'Date']);
    
    // Données
    foreach ($evaluations as $eval) {
        fputcsv($output, [
            $eval['employe'],
            $eval['departement'],
            $eval['critere'],
            $eval['note'],
            $eval['commentaire'],
            $eval['periode_fin']
        ]);
    }
    
    fclose($output);
    $filename = 'evaluations_' . date('Y-m-d') . '.xlsx';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer->save('php://output');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard DRH</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .dashboard { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h3 { margin: 0 0 10px 0; color: #333; }
        .stat-number { font-size: 2em; font-weight: bold; color: #007bff; }
        .critiques { color: #dc3545; }
        .excellentes { color: #28a745; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #007bff; color: white; }
        .btn { padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background-color: #218838; }
        .note { text-align: center; }
        .note-critique { background-color: #f8d7da; }
        .note-excellente { background-color: #d4edda; }
    </style>
</head>
<body>
    <div style="margin-bottom: 20px;">
        <button   class="btn" style="background-color: #6c757d;"><a href="index.php" style="color:white">retour</a></button>
    </div>
    
    <h1>Dashboard DRH - Gestion des Évaluations</h1>
    
    <!-- Dashboard Statistics -->
    <div class="dashboard">
        <div class="card">
            <h3>Total Évaluations</h3>
            <div class="stat-number"><?= $stats['total_evaluations'] ?></div>
        </div>
        
        <div class="card">
            <h3>Évaluations Critiques</h3>
            <div class="stat-number critiques"><?= $stats['evaluations_critiques'] ?></div>
            <small>(≤ 50%)</small>
        </div>
        <div class="card">
            <h3>Évaluations Excellentes</h3>
            <div class="stat-number excellentes"><?= $stats['evaluations_excellentes'] ?></div>
            <small>(≥ 80%)</small>
        </div>
    </div>
    
    <!-- Export Controls -->
    <div style="margin-bottom: 20px;">
        <form method="post" style="display: inline;">
            <button type="submit" name="export_excel" class="btn">📊 Exporter en Excel</button>
        </form>
    </div>
    
    <!-- Evaluations Table -->
    <table>
        <thead>
            <tr>
                <th>Employé</th>
                <th>Département</th>
                <th>Critère</th>
                <th>Note</th>
                <th>Commentaire</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($evaluations as $eval): ?>
            <tr class="<?= $eval['note'] <= 50 ? 'note-critique' : ($eval['note'] >= 80 ? 'note-excellente' : '') ?>">
                <td><?= htmlspecialchars($eval['employe']) ?></td>
                <td><?= htmlspecialchars($eval['departement']) ?></td>
                <td><?= htmlspecialchars($eval['critere']) ?></td>
                <td class="note"><?= htmlspecialchars($eval['note']) ?>%</td>
                <td><?= htmlspecialchars($eval['commentaire']) ?></td>
                <td><?= htmlspecialchars($eval['periode_fin']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
