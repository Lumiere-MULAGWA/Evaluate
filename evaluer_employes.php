<?php
session_start();
require_once 'db.php';

// Initialiser les variables d'erreur
$error_message = '';
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

// Vérification de l'authentification et du rôle
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['chef_service', 'chef_departement', 'drh'])) {
    header('Location: login.php');
    exit();
}

$evaluateur_id = $_SESSION['user_id'];
$user_name = $_SESSION['nom'] ?? 'Utilisateur';
$user_role = $_SESSION['role'];
$user_avatar = $_SESSION['avatar'] ?? 'assets/img/default-avatar.svg';

// Récupération des employés sélectionnés
$ids = $_POST['employes'] ?? $_SESSION['selected_employees'] ?? [];
if (!is_array($ids) || empty($ids)) {
    $_SESSION['error_message'] = "Aucun employé sélectionné pour l'évaluation.";
    header('Location: selection_employes.php');
    exit();
}

// Sauvegarder dans la session pour persistence
$_SESSION['selected_employees'] = $ids;

try {
    // Récupération des employés avec leurs informations complètes
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $sql = "SELECT u.*, d.nom as departement_nom 
            FROM utilisateurs u 
            LEFT JOIN departements d ON u.departement_id = d.id 
            WHERE u.id IN ($placeholders) AND u.role = 'employe'
            ORDER BY u.nom ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);
    $employes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($employes)) {
        $_SESSION['error_message'] = "Les employés sélectionnés n'ont pas été trouvés.";
        header('Location: selection_employes.php');
        exit();
    }

    // Critères d'évaluation avec descriptions
    $criteres = [
        'ponctualite' => [
            'nom' => 'Ponctualité',
            'description' => 'Respect des horaires et des délais',
            'icon' => 'fas fa-clock'
        ],
        'competence' => [
            'nom' => 'Compétences techniques',
            'description' => 'Maîtrise des compétences requises pour le poste',
            'icon' => 'fas fa-cogs'
        ],
        'travail_equipe' => [
            'nom' => 'Travail en équipe',
            'description' => 'Collaboration et communication avec les collègues',
            'icon' => 'fas fa-users'
        ],
        'initiative' => [
            'nom' => 'Initiative et autonomie',
            'description' => 'Prise d\'initiative et capacité à travailler de manière autonome',
            'icon' => 'fas fa-lightbulb'
        ],
        'qualite_travail' => [
            'nom' => 'Qualité du travail',
            'description' => 'Précision et qualité des tâches accomplies',
            'icon' => 'fas fa-star'
        ],
        'communication' => [
            'nom' => 'Communication',
            'description' => 'Capacité à communiquer efficacement',
            'icon' => 'fas fa-comments'
        ]
    ];

    // Récupérer les évaluations existantes pour pré-remplir le formulaire
    $existing_evaluations = [];
    if (!empty($employes)) {
        $eval_sql = "SELECT * FROM evaluations 
                     WHERE id_employe IN ($placeholders) 
                     AND id_evaluateur = ? 
                     AND annee = YEAR(NOW())";
        $eval_stmt = $pdo->prepare($eval_sql);
        $eval_params = array_merge($ids, [$evaluateur_id]);
        $eval_stmt->execute($eval_params);
        $existing_evals = $eval_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($existing_evals as $eval) {
            $existing_evaluations[$eval['id_employe']][$eval['critere']] = [
                'note' => $eval['note'],
                'commentaire' => $eval['commentaire']
            ];
        }
    }

    // Traitement de la soumission du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_evaluation'])) {
        $pdo->beginTransaction();
        
        try {
            $evaluations_saved = 0;
            
            foreach ($_POST['evaluations'] as $id_employe => $evaluation_data) {
                // Vérifier que l'employé fait partie de la sélection
                if (!in_array($id_employe, $ids)) continue;
                
                // Supprimer les anciennes évaluations de cet employé par cet évaluateur pour cette année
                $delete_stmt = $pdo->prepare("DELETE FROM evaluations 
                                              WHERE id_employe = ? 
                                              AND id_evaluateur = ? 
                                              AND annee = YEAR(NOW())");
                $delete_stmt->execute([$id_employe, $evaluateur_id]);
                
                foreach ($criteres as $critere_key => $critere_info) {
                    $note = $evaluation_data['notes'][$critere_key] ?? null;
                    $commentaire = trim($evaluation_data['commentaires'][$critere_key] ?? '');
                    
                    if ($note !== null && $note !== '' && $note >= 0 && $note <= 100) {
                        $insert_stmt = $pdo->prepare("INSERT INTO evaluations 
                                                      (id_employe, id_evaluateur, critere, note, commentaire, annee, date_creation) 
                                                      VALUES (?, ?, ?, ?, ?, YEAR(NOW()), NOW())");
                        $insert_stmt->execute([$id_employe, $evaluateur_id, $critere_key, $note, $commentaire]);
                        $evaluations_saved++;
                    }
                }
            }
            
            $pdo->commit();
            unset($_SESSION['selected_employees']);
            $_SESSION['success_message'] = "Évaluations enregistrées avec succès ! ($evaluations_saved évaluations sauvegardées)";
            
            $redirect_url = 'cs_dashboard.php';
            if ($user_role === 'chef_departement') {
                $redirect_url = 'cd_dashboard.php';
            } elseif ($user_role === 'drh') {
                $redirect_url = 'drh_dashboard.php';
            }
            
            header('Location: ' . $redirect_url);
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_message = "Erreur lors de l'enregistrement des évaluations : " . $e->getMessage();
        }
    }

} catch (PDOException $e) {
    $error_message = "Erreur de base de données : " . $e->getMessage();
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
