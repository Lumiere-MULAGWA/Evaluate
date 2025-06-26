<?php
session_start();
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}
$nom = $_SESSION['nom'];
$role = $_SESSION['role'];
$email = $_SESSION['email'];

// Obtenir les initiales pour l'avatar
$initiales = strtoupper(substr($nom, 0, 1));
if (strpos($nom, ' ') !== false) {
    $parts = explode(' ', $nom);
    $initiales = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
}

// Définir les informations du rôle
$role_info = [
    'drh' => [
        'title' => 'Directeur des Ressources Humaines',
        'icon' => 'fas fa-users-cog',
        'color' => '#e74c3c',
        'dashboard' => 'drh_dashboard.php'
    ],
    'chef_departement' => [
        'title' => 'Chef de Département',
        'icon' => 'fas fa-building',
        'color' => '#3498db',
        'dashboard' => 'cd_dashboard.php'
    ],
    'chef_service' => [
        'title' => 'Chef de Service',
        'icon' => 'fas fa-user-tie',
        'color' => '#2ecc71',
        'dashboard' => 'cs_dashboard.php'
    ],
    'employe' => [
        'title' => 'Employé',
        'icon' => 'fas fa-user',
        'color' => '#9b59b6',
        'dashboard' => '#'
    ]
];

$current_role = $role_info[$role] ?? $role_info['employe'];

// Données simulées pour les top performers (vous pouvez les récupérer de votre base de données)
$top_performers = [
    ['nom' => 'Marie Dupont', 'score' => 98, 'department' => 'Marketing', 'avatar' => 'MD'],
    ['nom' => 'Jean Martin', 'score' => 95, 'department' => 'Développement', 'avatar' => 'JM'],
    ['nom' => 'Sophie Chen', 'score' => 92, 'department' => 'Design', 'avatar' => 'SC']
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Evaluate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --gold: #ffd700;
            --silver: #c0c0c0;
            --bronze: #cd7f32;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --white: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            --shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--primary-gradient);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(255, 255, 255, 0.3); }
            50% { box-shadow: 0 0 40px rgba(255, 255, 255, 0.6); }
        }

        /* Container principal */
        .app-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Particules d'arrière-plan */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        /* Header moderne */
        .modern-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1rem 0;
            position: relative;
            z-index: 10;
            animation: slideInLeft 0.8s ease-out;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--white);
        }

        .brand-icon {
            width: 60px;
            height: 60px;
            background: var(--secondary-gradient);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: var(--shadow);
            animation: glow 3s ease-in-out infinite;
        }

        .brand-text h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.2rem;
            background: linear-gradient(45deg, #fff, #f8f9fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-text p {
            font-size: 0.9rem;
            opacity: 0.8;
            font-weight: 500;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--white);
            animation: slideInRight 0.8s ease-out;
        }

        .user-details h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
        }

        .user-details p {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .user-avatar {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: var(--white);
            color: var(--dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .user-avatar:hover {
            animation: pulse 0.6s ease-in-out;
        }

        /* Section principale */
        .main-content {
            flex: 1;
            padding: 3rem 2rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            position: relative;
            z-index: 5;
        }

        .hero-section {
            text-align: center;
            margin-bottom: 4rem;
            animation: fadeInUp 1s ease-out;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, #fff, #f8f9fa, #e9ecef);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.4rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .role-display {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 3rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .role-display:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        /* Grid des fonctionnalités */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .dashboard-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 2.5rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 1.2s ease-out;
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            transform: scale(0);
            transition: transform 0.6s ease;
        }

        .dashboard-card:hover::before {
            transform: scale(1);
        }

        .dashboard-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: var(--shadow-hover);
            background: rgba(255, 255, 255, 0.15);
        }

        .card-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: var(--white);
            position: relative;
            z-index: 2;
        }

        .card-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--white);
            position: relative;
            z-index: 2;
        }

        .card-description {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            margin-bottom: 2rem;
            position: relative;
            z-index: 2;
        }

        /* Top Performers Card */
        .top-performers {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 25px;
            padding: 2.5rem;
            box-shadow: var(--shadow);
            animation: fadeInUp 1.4s ease-out;
            margin-bottom: 3rem;
        }

        .performers-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .performers-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .performers-subtitle {
            color: #666;
            font-size: 1rem;
        }

        .performers-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .performer-card {
            background: var(--white);
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .performer-card:nth-child(1) {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: var(--dark);
        }

        .performer-card:nth-child(2) {
            background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%);
            color: var(--dark);
        }

        .performer-card:nth-child(3) {
            background: linear-gradient(135deg, #cd7f32 0%, #daa569 100%);
            color: var(--white);
        }

        .performer-card:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .performer-rank {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.2rem;
        }

        .performer-card:nth-child(1) .performer-rank {
            background: var(--gold);
            color: var(--dark);
        }

        .performer-card:nth-child(2) .performer-rank {
            background: var(--silver);
            color: var(--dark);
        }

        .performer-card:nth-child(3) .performer-rank {
            background: var(--bronze);
            color: var(--white);
        }

        .performer-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.4rem;
            margin: 0 auto 1rem;
            border: 3px solid rgba(255, 255, 255, 0.5);
        }

        .performer-name {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .performer-department {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 1rem;
        }

        .performer-score {
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
        }

        .score-label {
            font-size: 0.8rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Boutons d'action */
        .action-section {
            text-align: center;
            margin-top: 3rem;
        }

        .primary-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: var(--secondary-gradient);
            color: var(--white);
            padding: 1.2rem 3rem;
            border-radius: 50px;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 600;
            transition: all 0.4s ease;
            box-shadow: var(--shadow);
            margin: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .primary-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s ease;
        }

        .primary-btn:hover::before {
            left: 100%;
        }

        .primary-btn:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .secondary-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            padding: 1rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin: 0.5rem;
        }

        .secondary-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }

        /* Footer */
        .modern-footer {
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            color: var(--white);
            text-align: center;
            padding: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                padding: 0 1rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .performers-list {
                grid-template-columns: 1fr;
            }

            .main-content {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Particules d'arrière-plan -->
        <div class="particles">
            <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
            <div class="particle" style="left: 20%; animation-delay: 1s;"></div>
            <div class="particle" style="left: 30%; animation-delay: 2s;"></div>
            <div class="particle" style="left: 40%; animation-delay: 3s;"></div>
            <div class="particle" style="left: 50%; animation-delay: 4s;"></div>
            <div class="particle" style="left: 60%; animation-delay: 5s;"></div>
            <div class="particle" style="left: 70%; animation-delay: 2s;"></div>
            <div class="particle" style="left: 80%; animation-delay: 3s;"></div>
            <div class="particle" style="left: 90%; animation-delay: 1s;"></div>
        </div>

        <header class="modern-header">
            <div class="header-content">
                <div class="brand">
                    <div class="brand-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <div class="brand-text">
                        <h1>Evaluate</h1>
                        <p>Plateforme d'Excellence RH</p>
                    </div>
                </div>
                
                <div class="user-profile">
                    <div class="user-details">
                        <h3><?= htmlspecialchars($nom) ?></h3>
                        <p><?= htmlspecialchars($email) ?></p>
                    </div>
                    <div class="user-avatar">
                        <?= $initiales ?>
                    </div>
                </div>
            </div>
        </header>

        <main class="main-content">
            <section class="hero-section">
                <h1 class="hero-title">Bienvenue dans l'Excellence</h1>
                <p class="hero-subtitle">
                    Révolutionnez la gestion des talents avec notre plateforme d'évaluation nouvelle génération
                </p>
                
                <div class="role-display">
                    <i class="<?= $current_role['icon'] ?>"></i>
                    <?= $current_role['title'] ?>
                </div>
            </section>

            <!-- Top Performers Section -->
            <?php if ($role !== 'employe'): ?>
            <section class="top-performers">
                <div class="performers-header">
                    <h2 class="performers-title">
                        <i class="fas fa-trophy"></i>
                        Top Performers du Mois
                    </h2>
                    <p class="performers-subtitle">Les étoiles qui brillent dans notre organisation</p>
                </div>
                
                <div class="performers-list">
                    <?php foreach ($top_performers as $index => $performer): ?>
                    <div class="performer-card">
                        <div class="performer-rank"><?= $index + 1 ?></div>
                        <div class="performer-avatar">
                            <?= $performer['avatar'] ?>
                        </div>
                        <div class="performer-name"><?= $performer['nom'] ?></div>
                        <div class="performer-department"><?= $performer['department'] ?></div>
                        <div class="performer-score"><?= $performer['score'] ?>%</div>
                        <div class="score-label">Score Excellence</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Features Grid -->
            <section class="features-grid">
                <?php if ($role === 'drh'): ?>
                    <div class="dashboard-card">
                        <div class="card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="card-title">Analytics Avancés</h3>
                        <p class="card-description">
                            Explorez des insights profonds sur les performances organisationnelles avec des tableaux de bord interactifs
                        </p>
                    </div>
                    <div class="dashboard-card">
                        <div class="card-icon">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h3 class="card-title">Gestion Globale</h3>
                        <p class="card-description">
                            Orchestrez l'ensemble des talents de votre organisation avec des outils de gestion centralisés
                        </p>
                    </div>
                <?php elseif ($role === 'chef_departement'): ?>
                    <div class="dashboard-card">
                        <div class="card-icon">
                            <i class="fas fa-sitemap"></i>
                        </div>
                        <h3 class="card-title">Vision Département</h3>
                        <p class="card-description">
                            Pilotez votre département avec une vue d'ensemble des performances et des opportunités d'amélioration
                        </p>
                    </div>
                    <div class="dashboard-card">
                        <div class="card-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h3 class="card-title">Reporting Intelligent</h3>
                        <p class="card-description">
                            Générez des rapports personnalisés et prenez des décisions éclairées basées sur des données précises
                        </p>
                    </div>
                <?php elseif ($role === 'chef_service'): ?>
                    <div class="dashboard-card">
                        <div class="card-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="card-title">Évaluation 360°</h3>
                        <p class="card-description">
                            Menez des évaluations complètes de votre équipe avec des outils modernes et intuitifs
                        </p>
                    </div>
                    <div class="dashboard-card">
                        <div class="card-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h3 class="card-title">Planification Smart</h3>
                        <p class="card-description">
                            Organisez et planifiez les sessions d'évaluation avec intelligence et efficacité
                        </p>
                    </div>
                <?php endif; ?>
                
                <div class="dashboard-card">
                    <div class="card-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="card-title">Sécurité Maximale</h3>
                    <p class="card-description">
                        Vos données sont protégées par les plus hauts standards de sécurité et de confidentialité
                    </p>
                </div>
            </section>

            <section class="action-section">
                <?php if ($role !== 'employe'): ?>
                    <a href="<?= $current_role['dashboard'] ?>" class="primary-btn">
                        <i class="fas fa-rocket"></i>
                        Lancer le Dashboard
                    </a>
                <?php else: ?>
                    <div class="primary-btn" style="background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%); cursor: not-allowed;">
                        <i class="fas fa-info-circle"></i>
                        Espace Employé - Contactez votre Manager
                    </div>
                <?php endif; ?>
                
                <a href="logout.php" class="secondary-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </a>
            </section>
        </main>

        <footer class="modern-footer">
            <p>&copy; 2025 Evaluate - Plateforme d'Excellence RH | Powered by Innovation ✨
                <br>
                lmr lumiere
            </p>
        </footer>
    </div>

    <script>
        // Animation des particules
        document.addEventListener('DOMContentLoaded', function() {
            const particles = document.querySelectorAll('.particle');
            particles.forEach((particle, index) => {
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
            });

            // Animation d'apparition des cartes
            const cards = document.querySelectorAll('.dashboard-card, .performer-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = (index * 0.2) + 's';
            });

            // Effet parallax léger
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const particles = document.querySelector('.particles');
                particles.style.transform = `translateY(${scrolled * 0.5}px)`;
            });

            // Animation des compteurs pour les scores
            const scores = document.querySelectorAll('.performer-score');
            scores.forEach(score => {
                const target = parseInt(score.textContent);
                let current = 0;
                const increment = target / 50;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    score.textContent = Math.floor(current) + '%';
                }, 30);
            });
        });
    </script>
</body>
</html>
