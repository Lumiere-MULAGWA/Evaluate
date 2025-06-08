<?php
session_start();
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}
$nom = $_SESSION['nom'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Évaluation du Personnel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #2c3e50, #3498db);
            color: white;
            animation: fadeIn 1.2s ease-in;
        }
        header {
            padding: 20px;
            text-align: center;
            background-color: rgba(0, 0, 0, 0.2);
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        header h1 {
            margin: 0;
            font-size: 2.5rem;
        }
        .container {
            max-width: 700px;
            margin: 60px auto;
            text-align: center;
        }
        .btn {
            display: inline-block;
            background-color: #f39c12;
            color: white;
            padding: 12px 24px;
            margin: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 1.1rem;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background-color: #e67e22;
        }
        .role-box {
            margin-top: 40px;
            font-size: 1.3rem;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <header>
        <h1>Bienvenue, <?= htmlspecialchars($nom) ?> !</h1>
    </header>
    <div class="container">
        <p class="role-box">Vous êtes connecté en tant que : <strong><?= strtoupper($role) ?></strong></p>

        <?php if ($role === 'drh') : ?>
            <a href="drh_dashboard.php" class="btn"><i class="fas fa-chart-line"></i> Tableau de bord DRH</a>
        <?php elseif ($role === 'chef_departement') : ?>
            <a href="cd_dashboard.php" class="btn"><i class="fas fa-users"></i> Tableau de bord Chef Département</a>
        <?php elseif ($role === 'chef_service') : ?>
            <a href="cs_dashboard.php" class="btn"><i class="fas fa-user-check"></i> Tableau de bord Chef de Service</a>
        <?php endif; ?>

        <a href="logout.php" class="btn"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</body>
</html>
