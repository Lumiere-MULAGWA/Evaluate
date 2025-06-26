<?php
session_start();
require_once 'db.php'; // Inclure le fichier de connexion à la base de données

$error_message = '';

if (isset($_POST['login'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $mdp = $_POST['mot_de_passe'];

    if (empty($email) || empty($mdp)) {
        $error_message = 'Veuillez remplir tous les champs';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($mdp, $user['mot_de_passe'])) {
                // Créer les variables de session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['departement_id'] = $user['departement_id'] ?? null;

                // Redirection selon le rôle
                switch ($user['role']) { 
                    case 'drh':
                        header('Location: drh_dashboard.php');
                        exit();
                    case 'chef_departement':
                        header('Location: cd_dashboard.php');
                        exit();
                    case 'chef_service':
                        header('Location: cs_dashboard.php');
                        exit();
                    case 'employe':
                        header('Location: index.php');
                        exit();
                    default:
                        $error_message = 'Rôle utilisateur non reconnu';
                }
            } else {
                $error_message = 'Identifiants incorrects';
            }
        } catch (PDOException $e) {
            $error_message = 'Erreur de connexion à la base de données';
            error_log('Erreur de connexion: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Evaluate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .login-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            animation: slideInUp 0.8s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: var(--white);
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .login-logo {
            font-size: 3rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }
        
        .login-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .login-subtitle {
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .login-form {
            padding: 2rem;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .form-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            z-index: 1;
        }
        
        .form-control {
            padding-left: 3rem;
            height: 50px;
            border: 2px solid var(--gray-200);
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .form-control:focus + i {
            color: var(--primary-color);
        }
        
        .btn-login {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            border: none;
            border-radius: var(--border-radius);
            color: var(--white);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .alert {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: var(--border-radius);
            border-left: 4px solid;
            animation: slideInDown 0.5s ease-out;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .loading {
            display: none;
            margin-right: 0.5rem;
        }
        
        .btn-login.loading .loading {
            display: inline-block;
        }
        
        .btn-login.loading .btn-text {
            display: none;
        }
        
        .login-footer {
            text-align: center;
            padding: 1rem 2rem 2rem;
            color: var(--gray-600);
            font-size: var(--font-size-sm);
        }
        
        .version-info {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
            font-size: var(--font-size-xs);
            color: var(--gray-500);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-chart-line"></i>
            </div>
            <h1 class="login-title">Evaluate</h1>
            <p class="login-subtitle">Système d'évaluation du personnel</p>
        </div>
        
        <form method="POST" class="login-form" data-validate="true">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <input type="email" 
                       name="email" 
                       class="form-control" 
                       placeholder="Adresse e-mail" 
                       data-validate="required|email"
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                       required>
                <i class="fas fa-envelope"></i>
            </div>
            
            <div class="form-group">
                <input type="password" 
                       name="mot_de_passe" 
                       class="form-control" 
                       placeholder="Mot de passe" 
                       data-validate="required"
                       required>
                <i class="fas fa-lock"></i>
            </div>
            
            <button type="submit" name="login" class="btn-login">
                <span class="loading">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
                <span class="btn-text">
                    <i class="fas fa-sign-in-alt"></i>
                    Se connecter
                </span>
            </button>
        </form>
        
        <div class="login-footer">
            <p>Connectez-vous avec vos identifiants professionnels</p>
            <div class="version-info">
                <i class="fas fa-code"></i>
                Version 2.0 - Système sécurisé
            </div>
        </div>
    </div>
    
   
    <script>
        // Gestion du formulaire de connexion
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.login-form');
            const submitBtn = document.querySelector('.btn-login');
            
            form.addEventListener('submit', function(e) {
                // Animation de chargement
                submitBtn.classList.add('loading');
                
                // Validation côté client
                const email = form.querySelector('input[name="email"]').value;
                const password = form.querySelector('input[name="mot_de_passe"]').value;
                
                if (!email || !password) {
                    e.preventDefault();
                    submitBtn.classList.remove('loading');
                    Notifications.error('Veuillez remplir tous les champs');
                    return;
                }
                
                if (!Utils.isValidEmail(email)) {
                    e.preventDefault();
                    submitBtn.classList.remove('loading');
                    Notifications.error('Veuillez entrer une adresse email valide');
                    return;
                }
            });
        });
    </script>
</body>
</html>