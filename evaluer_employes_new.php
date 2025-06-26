<?php
session_start();
require_once 'db.php';

// Initialisation des variables
$error_message = '';
$employes = [];
$criteres = [];

// Vérification de l'authentification et du rôle
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['chef_service', 'chef_departement', 'drh'])) {
    header('Location: login.php');
    exit();
}

$evaluateur_id = $_SESSION['user_id'];
$user_name = $_SESSION['nom'] ?? 'Utilisateur';
$user_role = $_SESSION['role'];

// Récupération des employés sélectionnés
$ids = $_POST['employes'] ?? $_SESSION['selected_employees'] ?? [];
if (!is_array($ids) || empty($ids)) {
    $_SESSION['error_message'] = "Aucun employé sélectionné pour l'évaluation.";
    header('Location: cs_dashboard.php');
    exit();
}

// Sauvegarder dans la session pour persistence
$_SESSION['selected_employees'] = $ids;

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

try {
    // Récupération des employés avec leurs informations complètes
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $sql = "SELECT u.*, d.nom as departement_nom 
            FROM utilisateurs u 
            LEFT JOIN departements d ON u.id_departement = d.id 
            WHERE u.id IN ($placeholders) AND u.role = 'employe'
            ORDER BY u.nom ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);
    $employes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($employes)) {
        $_SESSION['error_message'] = "Les employés sélectionnés n'ont pas été trouvés.";
        header('Location: cs_dashboard.php');
        exit();
    }

    // Traitement de la soumission du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_evaluation'])) {
        $pdo->beginTransaction();
        
        try {
            $evaluations_saved = 0;
            
            foreach ($_POST['evaluations'] as $id_employe => $evaluation_data) {
                // Vérifier que l'employé fait partie de la sélection
                if (!in_array($id_employe, $ids)) continue;
                
                // Supprimer les anciennes évaluations de cet employé par cet évaluateur pour cette période
                $delete_stmt = $pdo->prepare("DELETE FROM evaluations 
                                              WHERE id_employe = ? 
                                              AND id_evaluateur = ? 
                                              AND periode_debut = CURDATE() 
                                              AND periode_fin = CURDATE()");
                $delete_stmt->execute([$id_employe, $evaluateur_id]);
                
                foreach ($criteres as $critere_key => $critere_info) {
                    $note = $evaluation_data['notes'][$critere_key] ?? null;
                    $commentaire = trim($evaluation_data['commentaires'][$critere_key] ?? '');
                    
                    if ($note !== null && $note !== '' && is_numeric($note)) {
                        $insert_stmt = $pdo->prepare("INSERT INTO evaluations 
                                                      (id_employe, id_evaluateur, critere, note, commentaire, 
                                                       periode_debut, periode_fin, statut, date_creation) 
                                                      VALUES (?, ?, ?, ?, ?, CURDATE(), CURDATE(), 'finalise', NOW())");
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
    // En cas d'erreur, on initialise une liste vide pour éviter les erreurs d'affichage
    if (empty($employes)) {
        $employes = [];
    }
}

// Vérification finale - si pas d'employés trouvés, rediriger
if (empty($employes) && empty($error_message)) {
    $_SESSION['error_message'] = "Aucun employé trouvé pour l'évaluation.";
    header('Location: cs_dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Évaluation du Personnel - Evaluate</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-chart-line"></i>
                    <span>Evaluate</span>
                </div>
                <div class="user-info">
                    <div class="user-avatar">
                        <?= strtoupper(substr($user_name, 0, 2)) ?>
                    </div>
                    <div class="user-details">
                        <h3><?= htmlspecialchars($user_name) ?></h3>
                        <span class="user-role">
                            <?= $user_role === 'chef_service' ? 'Chef de Service' : 
                                ($user_role === 'chef_departement' ? 'Chef de Département' : 'DRH') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <div class="container">
            <div class="nav-container">
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link"><i class="fas fa-home"></i> Accueil</a></li>
                    <li><a href="selection_employes.php" class="nav-link"><i class="fas fa-users"></i> Sélection</a></li>
                    <li><a href="#" class="nav-link active"><i class="fas fa-clipboard-check"></i> Évaluation</a></li>
                    <li><a href="<?= $user_role === 'chef_service' ? 'cs_dashboard.php' : ($user_role === 'chef_departement' ? 'cd_dashboard.php' : 'drh_dashboard.php') ?>" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a></li>
                    <li><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">
                        <i class="fas fa-clipboard-check"></i>
                        Évaluation du Personnel
                    </h1>
                    <div class="evaluation-progress">
                        <span class="progress-text">
                            <i class="fas fa-users"></i>
                            <?= count($employes) ?> employé<?= count($employes) > 1 ? 's' : '' ?> à évaluer
                        </span>
                    </div>
                </div>
            </div>

            <?php if (isset($error_message) && !empty($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error_message) ?>
                    <div style="margin-top: 10px;">
                        <a href="cs_dashboard.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($employes)): ?>

            <!-- Formulaire d'évaluation -->
            <form method="POST" id="evaluationForm" class="evaluation-form">
                <input type="hidden" name="submit_evaluation" value="1">
                
                <?php foreach ($ids as $id): ?>
                    <input type="hidden" name="employes[]" value="<?= htmlspecialchars($id) ?>">
                <?php endforeach; ?>

                <!-- Progress Bar -->
                <div class="evaluation-progress-section">
                    <div class="card">
                        <div class="card-body">
                            <div class="progress-header">
                                <h3>Progression de l'évaluation</h3>
                                <span class="progress-counter">0 / <?= count($employes) * count($criteres) ?> critères évalués</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" id="evaluationProgressBar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Évaluations par employé -->
                <?php foreach ($employes as $index => $employe): ?>
                    <div class="employee-evaluation-section" data-employee-id="<?= $employe['id'] ?>">
                        <div class="card">
                            <div class="card-header">
                                <div class="employee-header-info">
                                    <div class="employee-avatar">
                                        <?= strtoupper(substr($employe['nom'], 0, 2)) ?>
                                    </div>
                                    <div class="employee-details">
                                        <h2><?= htmlspecialchars($employe['nom']) ?></h2>
                                        <p class="employee-meta">
                                            <i class="fas fa-envelope"></i> <?= htmlspecialchars($employe['email']) ?>
                                            <?php if ($employe['departement_nom']): ?>
                                                <span class="separator">•</span>
                                                <i class="fas fa-building"></i> <?= htmlspecialchars($employe['departement_nom']) ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="employee-progress">
                                    <span class="employee-progress-text">0 / <?= count($criteres) ?> critères</span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar employee-progress-bar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="criteria-grid">
                                    <?php foreach ($criteres as $critere_key => $critere_info): ?>
                                        <div class="criteria-section">
                                            <div class="criteria-header">
                                                <div class="criteria-title">
                                                    <i class="<?= $critere_info['icon'] ?>"></i>
                                                    <h3><?= htmlspecialchars($critere_info['nom']) ?></h3>
                                                </div>
                                                <p class="criteria-description"><?= htmlspecialchars($critere_info['description']) ?></p>
                                            </div>

                                            <div class="rating-section">
                                                <label class="form-label">Évaluation (0-20 points)</label>
                                                <div class="rating-scale">
                                                    <?php for ($i = 0; $i <= 20; $i += 2): ?>
                                                        <div class="rating-option">
                                                            <input type="radio" 
                                                                   name="evaluations[<?= $employe['id'] ?>][notes][<?= $critere_key ?>]" 
                                                                   value="<?= $i ?>" 
                                                                   id="rating_<?= $employe['id'] ?>_<?= $critere_key ?>_<?= $i ?>"
                                                                   class="rating-input"
                                                                   data-employee="<?= $employe['id'] ?>"
                                                                   data-criteria="<?= $critere_key ?>">
                                                            <label for="rating_<?= $employe['id'] ?>_<?= $critere_key ?>_<?= $i ?>" class="rating-label">
                                                                <div class="rating-circle"><?= $i ?></div>
                                                                <span class="rating-text">
                                                                    <?php
                                                                    if ($i <= 6) echo "Insuffisant";
                                                                    elseif ($i <= 10) echo "Passable";
                                                                    elseif ($i <= 14) echo "Bien";
                                                                    elseif ($i <= 18) echo "Très bien";
                                                                    else echo "Excellent";
                                                                    ?>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>

                                            <div class="comment-section">
                                                <label class="form-label" for="comment_<?= $employe['id'] ?>_<?= $critere_key ?>">
                                                    <i class="fas fa-comment-alt"></i>
                                                    Commentaires et observations
                                                    <span class="comment-optional">(optionnel)</span>
                                                </label>
                                                <textarea name="evaluations[<?= $employe['id'] ?>][commentaires][<?= $critere_key ?>]" 
                                                          id="comment_<?= $employe['id'] ?>_<?= $critere_key ?>"
                                                          class="comment-textarea" 
                                                          placeholder="Détaillez votre évaluation : points forts, axes d'amélioration, suggestions..."
                                                          rows="4"></textarea>
                                                <div class="comment-counter">
                                                    <span class="char-count">0</span> / 500 caractères
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Actions -->
                <div class="evaluation-actions">
                    <div class="card">
                        <div class="card-body">
                            <div class="actions-content">
                                <div class="actions-info">
                                    <h3>Finaliser l'évaluation</h3>
                                    <p>Vérifiez que toutes les évaluations ont été complétées avant de soumettre.</p>
                                </div>
                                <div class="actions-buttons">
                                    <button type="button" class="btn btn-secondary" onclick="history.back()">
                                        <i class="fas fa-arrow-left"></i>
                                        Retour
                                    </button>
                                    <button type="button" class="btn btn-warning" id="previewBtn">
                                        <i class="fas fa-eye"></i>
                                        Aperçu
                                    </button>
                                    <button type="submit" class="btn btn-success" id="submitBtn" disabled>
                                        <i class="fas fa-save"></i>
                                        Enregistrer les évaluations
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Aucun employé sélectionné</h3>
                    <p>Aucun employé n'a été trouvé pour l'évaluation. Veuillez retourner à la sélection des employés.</p>
                    <div style="margin-top: 15px;">
                        <a href="cs_dashboard.php" class="btn btn-primary">
                            <i class="fas fa-users"></i> Sélectionner des employés
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal d'aperçu -->
    <div class="modal" id="previewModal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">Aperçu des évaluations</h3>
                <button type="button" class="modal-close" onclick="closeModal('previewModal')">&times;</button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Le contenu sera généré par JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('previewModal')">Fermer</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/app.js"></script>
    <script>
        // Initialisation de l'évaluation
        document.addEventListener('DOMContentLoaded', function() {
            EvaluationManager.init();
            
            // Afficher message de succès/erreur
            <?php if (isset($_SESSION['success_message'])): ?>
                Notifications.success('<?= addslashes($_SESSION['success_message']) ?>');
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                Notifications.error('<?= addslashes($_SESSION['error_message']) ?>');
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>
