<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// 1. Vérifier le statut actuel de la candidature
$stmt_cand = $conn->prepare("SELECT statut FROM candidature WHERE user_id = ?");
$stmt_cand->bind_param("i", $user_id);
$stmt_cand->execute();
$res_cand = $stmt_cand->get_result();
$check_soumission = $res_cand->fetch_assoc();
$deja_soumis = ($check_soumission && $check_soumission['statut'] === 'soumise');

// 2. Récupérer les données avec des requêtes préparées pour plus de sécurité
$stmt_info = $conn->prepare("SELECT * FROM informations WHERE user_id = ?");
$stmt_info->bind_param("i", $user_id);
$stmt_info->execute();
$info = $stmt_info->get_result()->fetch_assoc();

$stmt_docs = $conn->prepare("SELECT * FROM documents WHERE user_id = ?");
$stmt_docs->bind_param("i", $user_id);
$stmt_docs->execute();
$docs = $stmt_docs->get_result()->fetch_assoc();

// 3. Action de soumission
if (isset($_POST['submit_candidature']) && !$deja_soumis) {
    // Vérification de sécurité : on ne soumet pas si les données sont vides
    if ($info && $docs) {
        $stmt = $conn->prepare("REPLACE INTO candidature (user_id, statut, date_soumission) VALUES (?, 'soumise', NOW())");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            // Optionnel : Rediriger directement vers le reçu après 2 secondes ou rester ici
            $deja_soumis = true;
            header("Location: verification.php?success=1"); // Redirection pour éviter le double envoi
            exit;
        }
    } else {
        $message = "Erreur : Votre dossier est incomplet. Veuillez remplir toutes les étapes.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification finale</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .summary { background: #fdfdfd; border: 1px solid #eee; padding: 15px; border-radius: 8px; text-align: left; margin-bottom: 20px; }
        .summary h3 { border-bottom: 1px solid #3498db; color: #3498db; padding-bottom: 5px; }
        .item { margin-bottom: 8px; font-size: 0.95em; }
        .success-box { background: #d4edda; color: #155724; padding: 30px; border-radius: 8px; text-align: center; border: 1px solid #c3e6cb; }
        .error-message { color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .btn-submit { flex: 2; background: #27ae60; color: white; border: none; padding: 12px; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        .btn-submit:hover { background: #219150; }
    </style>
</head>
<body>
<div class="container" style="max-width: 700px; margin: 40px auto; font-family: sans-serif;">
    <h2>✅ Vérification de votre dossier</h2>

    <?php if (isset($_GET['success']) || $deja_soumis): ?>
        <div class="success-box">
            <div style="font-size: 50px;">🎉</div>
            <h3>Dossier Soumis avec Succès !</h3>
            <p>Votre demande a bien été enregistrée et est en attente de validation.</p>
            <hr>
            <p>Vous pouvez maintenant télécharger votre justificatif de dépôt :</p>
            <a href="recu_depot.php" class="btn-primary" style="display:inline-block; background:#8e44ad; color:white; padding: 12px 25px; text-decoration:none; border-radius: 5px; font-weight: bold;">📄 Télécharger mon Récépissé (PDF)</a>
            <br><br>
            <a href="dashboard.php" style="color: #7f8c8d;">Retour au tableau de bord</a>
        </div>
    <?php else: ?>
        
        <?php if($message): ?>
            <div class="error-message"><?= $message ?></div>
        <?php endif; ?>

        <p>Veuillez relire attentivement vos informations avant l'envoi définitif.</p>

        <div class="summary">
            <h3>Informations Personnelles</h3>
            <?php if($info): ?>
                <div class="item"><strong>Type de demande :</strong> <?= $info['renouvellement'] ? 'Renouvellement' : 'Premier Titre de séjour' ?></div>
                <div class="item"><strong>Numéro de Passeport :</strong> <?= htmlspecialchars($info['num_passport']) ?></div>
                <div class="item"><strong>Nationalité :</strong> <?= htmlspecialchars($info['nationalite']) ?></div>
            <?php else: ?>
                <p style="color:red;">⚠️ Informations manquantes.</p>
            <?php endif; ?>
        </div>

        <div class="summary">
            <h3>Documents Justificatifs</h3>
            <?php if($docs): ?>
                <div class="item">✅ Passeport (Scan) : <span style="color:green">Téléchargé</span></div>
                <div class="item">✅ Extrait de Naissance : <span style="color:green">Téléchargé</span></div>
                <?php if(isset($info['renouvellement']) && $info['renouvellement'] && !empty($docs['carte_sejour'])): ?>
                    <div class="item">✅ Ancienne Carte de séjour : <span style="color:green">Téléchargé</span></div>
                <?php endif; ?>
            <?php else: ?>
                <p style="color:red;">⚠️ Aucun document téléversé.</p>
            <?php endif; ?>
        </div>

        <form method="POST" onsubmit="return confirm('Confirmez-vous que toutes les informations saisies sont exactes ?');">
            <p style="background: #fff3cd; padding: 10px; border-radius: 5px; font-size: 0.85em;">
                <strong>Attention :</strong> Une fois le dossier envoyé, vous ne pourrez plus modifier vos informations de manière autonome.
            </p>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <a href="remplir_info.php" style="flex: 1; text-align: center; padding: 12px; background: #95a5a6; color: white; text-decoration: none; border-radius: 5px;">✏️ Modifier</a>
                <button type="submit" name="submit_candidature" class="btn-submit" <?= (!$info || !$docs) ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : '' ?>>
                    🚀 Envoyer mon dossier
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>
</body>
</html>