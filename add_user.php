<?php
session_start();
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'drh') {
//     header('Location: login.php');
//     exit();
// }

require 'db.php';

$errors = [];
$success = '';

$stmt_services = $pdo->query("SELECT id, nom FROM services ORDER BY nom");
$services = $stmt_services->fetchAll();



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $motdepasse = $_POST['motdepasse'] ?? '';
    $role = $_POST['role'] ?? '';
    $id_service = $_POST['id_service'] ?? null;
    $prenom = $_POST['prenom'] ?? '';

    if (!$nom || !$email || !$motdepasse || !$role || !$id_service) {
        $errors[] = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    } elseif (!in_array($role, ['drh', 'chef_departement', 'chef_service' ,'employe'])) {
        $errors[] = "Rôle invalide.";
    } else {
        // Vérifier que le service existe bien
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM services WHERE id = ?");
        $stmt->execute([$id_service]);
        if ($stmt->fetchColumn() == 0) {
            $errors[] = "Service sélectionné invalide.";
        }
    }

    if (!$errors) {
        $hash = password_hash($motdepasse, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom,prenom , email, mot_de_passe, role, id_service ) VALUES (?, ?, ?, ?, ?,?)");
        $result = $stmt->execute([$nom,$prenom, $email, $hash, $role, $id_service]);

        if ($result) {
            $success = "Utilisateur ajouté avec succès.";
        } else {
            $errors[] = "Erreur lors de l'ajout en base.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un utilisateur</title>
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
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            text-align: center;
            padding: 30px;
        }

        .header h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 16px;
        }

        .form-container {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        input, select {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #3498db;
            background: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 16px;
        }

        button {
            width: 100%;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3);
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
        }

        .alert-success {
            background: #efe;
            border: 1px solid #cfc;
            color: #363;
        }

        .alert ul {
            margin: 0;
            padding-left: 20px;
        }

        .role-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            margin-left: 8px;
        }

        .badge-drh { background: #e74c3c; color: white; }
        .badge-chef { background: #f39c12; color: white; }
        .badge-employe { background: #27ae60; color: white; }

        @media (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 10px;
            }
            
            .form-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <i class="fas fa-user-plus" style="font-size: 40px; margin-bottom: 15px;"></i>
        <h2>Nouvel Utilisateur</h2>
        <p>Ajoutez un nouvel utilisateur au système</p>
    </div>

    <div class="form-container">
        <?php if ($errors): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="nom">Nom  </label>
                <div class="input-wrapper">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" id="nom" name="nom" required 
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" 
                           placeholder="Entrez le nom complet" />
                </div>
            </div>

            <div class="form-group">
                <label for="nom">Prenom </label>
                <div class="input-wrapper">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" id="nom" name="nom" required 
                           value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" 
                           placeholder="Entrez le prenom" />
                </div>
            </div>

            <div class="form-group">
                <label for="email">Adresse email *</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" required 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           placeholder="exemple@entreprise.com" />
                </div>
            </div>

            <div class="form-group">
                <label for="motdepasse">Mot de passe *</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="motdepasse" name="motdepasse" required 
                           placeholder="Mot de passe sécurisé" />
                </div>
            </div>

            <div class="form-group">
                <label for="role">Rôle dans l'entreprise *</label>
                <div class="input-wrapper">
                    <i class="fas fa-id-badge input-icon"></i>
                    <select id="role" name="role" required>
                        <option value="">-- Sélectionnez un rôle --</option>
                        <option value="employe" <?= (($_POST['role'] ?? '') === 'employe') ? 'selected' : '' ?>>
                            Employé <span class="badge-employe">👥</span>
                        </option>
                        <option value="chef_service" <?= (($_POST['role'] ?? '') === 'chef_service') ? 'selected' : '' ?>>
                            Chef de service <span class="badge-chef">👨‍💼</span>
                        </option>
                        <option value="chef_departement" <?= (($_POST['role'] ?? '') === 'chef_departement') ? 'selected' : '' ?>>
                            Chef de département <span class="badge-chef">🏢</span>
                        </option>
                        <option value="drh" <?= (($_POST['role'] ?? '') === 'drh') ? 'selected' : '' ?>>
                            DRH <span class="badge-drh">⭐</span>
                        </option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="id_service">Service d'affectation *</label>
                <div class="input-wrapper">
                    <i class="fas fa-building input-icon"></i>
                    <select id="id_service" name="id_service" required>
                        <option value="">-- Choisissez un service --</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?= $service['id'] ?>" 
                                    <?= (($_POST['id_service'] ?? '') == $service['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($service['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <button type="submit">
                <i class="fas fa-user-plus"></i>
                Créer l'utilisateur
            </button>
        </form>
    </div>
</div>

</body>
</html>
