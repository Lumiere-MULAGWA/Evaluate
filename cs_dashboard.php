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
        }
        
        .employee-card:hover {
            border-color: #0072ff;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 114, 255, 0.15);
        }
        
        .employee-card.selected {
            border-color: #0072ff;
            background: #f0f8ff;
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
        }
        
        .checkbox-custom {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 24px;
            height: 24px;
            border: 2px solid #ddd;
            border-radius: 6px;
            background: white;
            transition: all 0.3s ease;
        }
        
        .checkbox-custom::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .checkbox-input:checked + .checkbox-custom {
            background: #0072ff;
            border-color: #0072ff;
        }
        
        .checkbox-input:checked + .checkbox-custom::after {
            transform: translate(-50%, -50%) scale(1);
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
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 200px;
        }
        
        .submit-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 114, 255, 0.3);
        }
        
        .submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
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
    
    <form method="POST" action="evaluer_employes.php" id="evaluationForm">
        <div class="content">
            <div class="employee-grid">
                <?php foreach ($employes as $emp): ?>
                    <label class="employee-card" for="emp_<?= $emp['id'] ?>">
                        <input type="checkbox" name="employes[]" value="<?= $emp['id'] ?>" 
                               class="checkbox-input" id="emp_<?= $emp['id'] ?>">
                        <div class="checkbox-custom"></div>
                        
                        <div class="employee-info">
                            <div class="employee-avatar">
                                <?= strtoupper(substr($emp['nom'], 0, 1)) ?>
                            </div>
                            <div class="employee-details">
                                <h3><?= htmlspecialchars($emp['nom']) ?></h3>
                                <p><?= htmlspecialchars($emp['email']) ?></p>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="action-bar">
            <span class="selected-count" id="selectedCount">0 employé(s) sélectionné(s)</span>
            <button type="submit" class="submit-btn" id="submitBtn" disabled>
                Lancer l'évaluation
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.checkbox-input');
    const selectedCount = document.getElementById('selectedCount');
    const submitBtn = document.getElementById('submitBtn');
    const cards = document.querySelectorAll('.employee-card');
    
    function updateSelection() {
        const checked = document.querySelectorAll('.checkbox-input:checked').length;
        selectedCount.textContent = `${checked} employé(s) sélectionné(s)`;
        submitBtn.disabled = checked === 0;
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const card = this.closest('.employee-card');
            if (this.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
            updateSelection();
        });
    });
    
    cards.forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                const checkbox = this.querySelector('.checkbox-input');
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
    });
});
</script>

</body>
</html>
