<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_service') {
    header('Location: login.php');
    exit();
}
include('db.php');

$chef_id = $_SESSION['user_id'];
$req = $pdo->prepare("SELECT u.* FROM utilisateurs u 
    JOIN utilisateurs chef ON u.id_service = chef.id_service 
    WHERE chef.id = ? AND u.role = 'employe'");
$req->execute([$chef_id]);
$employes = $req->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sélectionner les employés à évaluer</title>
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
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #0072ff, #00c6ff);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h2 {
            font-size: 28px;
            font-weight: 300;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px;
        }
        
        .employee-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .employee-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        
        .employee-card:hover {
            border-color: #0072ff;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 114, 255, 0.15);
        }
        
        .employee-card.selected {
            border-color: #0072ff;
            background: #f0f8ff;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 114, 255, 0.2);
        }
        
        .employee-card.selected::after {
            content: '✓ Sélectionné';
            position: absolute;
            top: 10px;
            right: 15px;
            background: #0072ff;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .employee-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .employee-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #0072ff, #00c6ff);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }
        
        .employee-details h3 {
            font-size: 16px;
            margin-bottom: 5px;
            color: #333;
        }
        
        .employee-details p {
            color: #666;
            font-size: 14px;
        }
        
        .checkbox-input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            pointer-events: none;
        }
        
        .selection-indicator {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 30px;
            height: 30px;
            border: 2px solid #ddd;
            border-radius: 50%;
            background: white;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            opacity: 0;
        }
        
        .employee-card:hover .selection-indicator {
            opacity: 1;
        }
        
        .employee-card.selected .selection-indicator {
            opacity: 1;
            background: #0072ff;
            border-color: #0072ff;
            color: white;
        }
        
        .action-bar {
            background: #f8f9fa;
            padding: 20px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .selected-count {
            color: #666;
            font-size: 14px;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #0072ff, #00c6ff);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 250px;
            font-weight: 600;
        }
        
        .submit-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 114, 255, 0.3);
        }
        
        .submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 10px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
        
        @media (max-width: 768px) {
            .employee-grid {
                grid-template-columns: 1fr;
            }
            
            .action-bar {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>

<div class="container">
     
    <div class="header">
        <h2>Évaluation des employés</h2>
        <p>Sélectionnez les employés que vous souhaitez évaluer</p>
    </div>
    <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Retour
            </a>
    <form method="POST" action="evaluer_employes_new.php" id="evaluationForm">
        <div class="content">
            <div class="employee-grid">
                <?php foreach ($employes as $emp): ?>
                    <div class="employee-card" data-employee-id="<?= $emp['id'] ?>" data-employee-name="<?= htmlspecialchars($emp['nom']) ?>">
                        <input type="checkbox" name="employes[]" value="<?= $emp['id'] ?>" 
                               class="checkbox-input" id="emp_<?= $emp['id'] ?>">
                        <div class="selection-indicator">✓</div>
                        
                        <div class="employee-info">
                            <div class="employee-avatar">
                                <?= strtoupper(substr($emp['nom'], 0, 1)) ?>
                            </div>
                            <div class="employee-details">
                                <h3><?= htmlspecialchars($emp['nom']) ?></h3>
                                <p><?= htmlspecialchars($emp['email']) ?></p>
                                <?php if (!empty($emp['prenom'])): ?>
                                    <p style="font-size: 12px; color: #888;">
                                        <?= htmlspecialchars($emp['prenom']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="action-bar">
            <div>
                <span class="selected-count" id="selectedCount">0 employé(s) sélectionné(s)</span>
                <br>
                <small style="color: #888; font-size: 12px;">Cliquez sur les cartes pour sélectionner les employés</small>
            </div>
            <div>
                <button type="button" class="btn-secondary" onclick="clearSelection()">
                    Tout désélectionner
                </button>
                <button type="submit" class="submit-btn" id="submitBtn" disabled>
                    Commencer l'évaluation →
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.employee-card');
    const selectedCount = document.getElementById('selectedCount');
    const submitBtn = document.getElementById('submitBtn');
    
    function updateSelection() {
        const selectedCards = document.querySelectorAll('.employee-card.selected');
        const count = selectedCards.length;
        
        selectedCount.textContent = `${count} employé(s) sélectionné(s)`;
        submitBtn.disabled = count === 0;
        
        if (count > 0) {
            submitBtn.textContent = `Évaluer ${count} employé${count > 1 ? 's' : ''} →`;
        } else {
            submitBtn.textContent = 'Commencer l\'évaluation →';
        }
    }
    
    function toggleEmployeeSelection(card) {
        const checkbox = card.querySelector('.checkbox-input');
        const isSelected = card.classList.contains('selected');
        
        if (isSelected) {
            // Désélectionner
            card.classList.remove('selected');
            checkbox.checked = false;
        } else {
            // Sélectionner
            card.classList.add('selected');
            checkbox.checked = true;
            
            // Animation de feedback
            card.style.transform = 'scale(0.98)';
            setTimeout(() => {
                card.style.transform = '';
            }, 150);
        }
        
        updateSelection();
    }
    
    // Gestion du clic sur les cartes
    cards.forEach(card => {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            toggleEmployeeSelection(this);
        });
        
        // Animation hover améliorée
        card.addEventListener('mouseenter', function() {
            if (!this.classList.contains('selected')) {
                this.style.borderColor = '#0072ff';
                this.style.transform = 'translateY(-2px)';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            if (!this.classList.contains('selected')) {
                this.style.borderColor = 'transparent';
                this.style.transform = '';
            }
        });
    });
    
    // Fonction pour tout désélectionner
    window.clearSelection = function() {
        cards.forEach(card => {
            card.classList.remove('selected');
            card.querySelector('.checkbox-input').checked = false;
        });
        updateSelection();
    };
    
    // Validation avant soumission
    document.getElementById('evaluationForm').addEventListener('submit', function(e) {
        const selectedCards = document.querySelectorAll('.employee-card.selected');
        
        if (selectedCards.length === 0) {
            e.preventDefault();
            alert('Veuillez sélectionner au moins un employé à évaluer.');
            return false;
        }
        
        // Confirmation avec les noms des employés sélectionnés
        const employeeNames = Array.from(selectedCards).map(card => 
            card.getAttribute('data-employee-name')
        );
        
        const confirmMessage = `Voulez-vous commencer l'évaluation de ${selectedCards.length} employé(s) ?\n\n` +
                              `Employés sélectionnés :\n• ${employeeNames.join('\n• ')}`;
        
        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }
        
        // Animation de chargement
        submitBtn.textContent = 'Préparation...';
        submitBtn.style.background = '#28a745';
        submitBtn.disabled = true;
    });
    
    // Initialisation
    updateSelection();
});
</script>

</body>
</html>
