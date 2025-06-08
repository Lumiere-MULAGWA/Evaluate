<!-- login.php -->
<?php include('db.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #00c6ff, #0072ff);
            margin: 0;
        }

        .login-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .login-box h2 {
            margin-bottom: 20px;
            color: #0072ff;
        }

        .login-box input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .login-box button {
            background: #0072ff;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 8px;
            width: 100%;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .login-box button:hover {
            background: #005ec4;
        }
    </style>
</head>
<body>
    <form method="POST" class="login-box">
        <h2>Connexion</h2>
        <input type="email" name="email" placeholder="Adresse e-mail" required>
        <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
        <button type="submit" name="login">Se connecter</button>
    </form>

<?php
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $mdp = $_POST['mot_de_passe'];

    $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($mdp, $user['mot_de_passe'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        // Redirection selon le rôle
        switch ($user['role']) {
            case 'drh':
                header('Location: drh_dashboard.php');
                break;
            case 'chef_departement':
                header('Location: cd_dashboard.php');
                break;
            case 'chef_service':
                header('Location: cs_dashboard.php');
                break;
            case 'employe':
                echo "<p>Vous êtes évalué. Pas d accès au tableau de bord.</p>";
                break;
        }
    } else {
        echo "<p style='color:red;text-align:center;'>Identifiants incorrects</p>";
    }
}
?>
</body>
</html>
