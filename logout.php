<?php
session_start();

// Détruire toutes les données de session
$_SESSION = array();

// Détruire le cookie de session si il existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruire la session
session_destroy();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déconnexion - Evaluate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-family);
            margin: 0;
            padding: 1rem;
        }
        
        .logout-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            text-align: center;
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
        
        .logout-header {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: var(--white);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .logout-header::before {
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
        
        .logout-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }
        
        .logout-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .logout-subtitle {
            opacity: 0.9;
            position: relative;
            z-index: 1;
            font-size: 1rem;
        }
        
        .logout-body {
            padding: 2rem;
        }
        
        .success-message {
            color: var(--success-color);
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .logout-info {
            color: var(--gray-600);
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .btn-login {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            border: none;
            border-radius: var(--border-radius);
            color: var(--white);
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
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
        
        .logout-footer {
            padding: 1rem 2rem 2rem;
            color: var(--gray-500);
            font-size: var(--font-size-sm);
        }
        
        .security-note {
            background: var(--gray-100);
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-top: 1.5rem;
            border-left: 4px solid var(--warning-color);
        }
        
        .security-note i {
            color: var(--warning-color);
            margin-right: 0.5rem;
        }
        
        .countdown {
            font-weight: 600;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-header">
            <div class="logout-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <h1 class="logout-title">À bientôt !</h1>
            <p class="logout-subtitle">Vous avez été déconnecté avec succès</p>
        </div>
        
        <div class="logout-body">
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                Déconnexion réussie
            </div>
            
            <p class="logout-info">
                Votre session a été fermée en toute sécurité. Merci d'avoir utilisé le système d'évaluation du personnel.
            </p>
            
            <a href="login.php" class="btn-login">
                <i class="fas fa-sign-in-alt"></i>
                Se reconnecter
            </a>
            
            <div class="security-note">
                <i class="fas fa-shield-alt"></i>
                <strong>Note de sécurité :</strong> Fermez votre navigateur si vous utilisez un ordinateur partagé.
            </div>
        </div>
        
        <div class="logout-footer">
            <p>Redirection automatique dans <span class="countdown" id="countdown">10</span> secondes...</p>
        </div>
    </div>
    
    <script>
        // Compte à rebours pour la redirection
        let countdown = 10;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = 'login.php';
            }
        }, 1000);
        
        // Animation des éléments
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.success-message, .logout-info, .btn-login, .security-note');
            elements.forEach((element, index) => {
                setTimeout(() => {
                    element.style.animation = 'fadeInUp 0.6s ease-out forwards';
                    element.style.opacity = '0';
                    element.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        element.style.opacity = '1';
                        element.style.transform = 'translateY(0)';
                    }, 50);
                }, index * 200);
            });
        });
    </script>
</body>
</html>
