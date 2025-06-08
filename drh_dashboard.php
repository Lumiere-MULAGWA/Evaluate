<?php
require('vendor/autoload.php');
require('db.php');
use Dompdf\Dompdf;

// Récupérer les évaluations critiques
$requete = $pdo->query("SELECT u.nom AS employe, d.nom AS departement, e.critere, e.note, e.commentaire
                         FROM evaluations e
                         JOIN utilisateurs u ON e.id_employe = u.id
                         JOIN departements d ON u.id_departement = d.id
                         WHERE e.note <= 50");
$critiques = $requete->fetchAll();

// Créer le HTML du PDF
ob_start();
?>
<html>
<head>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #003366; color: white; }
    </style>
</head>
<body>
    <h2>Rapport des évaluations critiques (<= 50%)</h2>
    <table>
        <thead>
            <tr>
                <th>Employé</th>
                <th>Département</th>
                <th>Critère</th>
                <th>Note</th>
                <th>Commentaire</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($critiques as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['employe']) ?></td>
                <td><?= htmlspecialchars($c['departement']) ?></td>
                <td><?= htmlspecialchars($c['critere']) ?></td>
                <td><?= htmlspecialchars($c['note']) ?>%</td>
                <td><?= htmlspecialchars($c['commentaire']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
<?php
$html = ob_get_clean();

// Générer le PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$output = $dompdf->output();

// Enregistrer le fichier temporairement
$filePath = 'rapport_evaluation.pdf';
file_put_contents($filePath, $output);

// Envoi par mail
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.example.com'; // Remplacer par votre serveur SMTP
    $mail->SMTPAuth = true;
    $mail->Username = 'votre-email@example.com';
    $mail->Password = 'votre-mot-de-passe';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('votre-email@example.com', 'Application RH');
    $mail->addAddress('drh@example.com', 'DRH');
    $mail->Subject = 'Rapport des évaluations critiques';
    $mail->Body = 'Bonjour, veuillez trouver ci-joint le rapport des évaluations critiques.';
    $mail->addAttachment($filePath);

    $mail->send();
    echo "Rapport PDF généré et envoyé avec succès.";
} catch (Exception $e) {
    echo "Erreur lors de l'envoi : {$mail->ErrorInfo}";
}
?>
