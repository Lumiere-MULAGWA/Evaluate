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

// Traitement des commentaires du chef de département
if (isset($_POST['save_comments'])) {
    foreach ($_POST['commentaires_cd'] as $id_emp => $commentaire) {
        if (!empty($commentaire)) {
            $stmt = $pdo->prepare("UPDATE evaluations SET commentaire_cd = ? WHERE id_employe = ?");
            $stmt->execute([$commentaire, $id_emp]);
        }
    }
    echo "<script>alert('Commentaires sauvegardés avec succès !');</script>";
}

// Soumettre au DRH
if (isset($_POST['soumettre'])) {
    $stmt = $pdo->prepare("INSERT INTO soumissions (id_cd, annee) VALUES (?, YEAR(NOW()))");
    $stmt->execute([$id_cd]);
    echo "<script>alert('Soumission envoyée au DRH !');</script>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chef de Département - Evaluations</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 300;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .back-btn {
            position: absolute;
            left: 30px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-50%) translateX(-5px);
        }

        .content {
            padding: 40px;
        }

        .employee-card {
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 30px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }

        .employee-card:hover {
            transform: translateY(-2px);
        }

        .employee-header {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 20px;
            font-size: 1.3em;
            font-weight: 500;
        }

        .evaluations-grid {
            padding: 25px;
        }

        .evaluation-item {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .evaluation-item.danger {
            border-left-color: #e74c3c;
            background: #fdf2f2;
        }

        .eval-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .critere {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1em;
        }

        .note {
            background: #667eea;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
        }

        .note.danger {
            background: #e74c3c;
        }

        .commentaire {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-style: italic;
            color: #666;
        }

        .comment-section {
            border-top: 2px solid #eee;
            padding-top: 20px;
        }

        .comment-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .comment-textarea {
            width: 100%;
            min-height: 100px;
            padding: 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            transition: border-color 0.3s ease;
        }

        .comment-textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #eee;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-save {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.3);
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 4em;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: #999;
        }

        @media (max-width: 768px) {
            .header h1 { font-size: 2em; }
            .content { padding: 20px; }
            .action-buttons { flex-direction: column; }
            .back-btn { position: static; transform: none; margin-bottom: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Retour
            </a>
            <h1><i class="fas fa-chart-line"></i> Tableau de bord</h1>
            <p>Chef de Département - Gestion des Évaluations</p>
        </div>

        <div class="content">
            <form method="post">
                <?php if (empty($groupes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Aucune évaluation disponible</h3>
                        <p>Aucune évaluation n'a été reçue pour l'instant.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($groupes as $id_emp => $data): ?>
                        <div class="employee-card">
                            <div class="employee-header">
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($data['nom']) ?>
                            </div>
                            
                            <div class="evaluations-grid">
                                <?php foreach ($data['evaluations'] as $eval): ?>
                                    <div class="evaluation-item <?= $eval['note'] <= 50 ? 'danger' : '' ?>">
                                        <div class="eval-header">
                                            <span class="critere"><?= htmlspecialchars($eval['critere']) ?></span>
                                            <span class="note <?= $eval['note'] <= 50 ? 'danger' : '' ?>">
                                                <?= htmlspecialchars($eval['note']) ?>%
                                            </span>
                                        </div>
                                        
                                        <?php if (!empty($eval['commentaire'])): ?>
                                            <div class="commentaire">
                                                <strong>Commentaire du chef de service :</strong><br>
                                                <?= htmlspecialchars($eval['commentaire']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div class="comment-section">
                                    <label class="comment-label">
                                        <i class="fas fa-comment-dots"></i>
                                        Votre commentaire en tant que Chef de Département :
                                    </label>
                                    <textarea 
                                        name="commentaires_cd[<?= $id_emp ?>]" 
                                        class="comment-textarea"
                                        placeholder="Ajoutez votre commentaire sur l'évaluation globale de cet employé..."
                                    ><?= isset($eval['commentaire_cd']) ? htmlspecialchars($eval['commentaire_cd']) : '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="action-buttons">
                        <button type="submit" name="save_comments" class="btn btn-save">
                            <i class="fas fa-save"></i>
                            Sauvegarder les commentaires
                        </button>
                        <button type="submit" name="soumettre" class="btn btn-submit">
                            <i class="fas fa-paper-plane"></i>
                            Soumettre au DRH
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>
