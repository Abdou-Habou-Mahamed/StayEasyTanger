<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupération des données
$stmt_info = $conn->prepare("SELECT * FROM informations WHERE user_id = ?");
$stmt_info->bind_param("i", $user_id);
$stmt_info->execute();
$info = $stmt_info->get_result()->fetch_assoc();

$stmt_cand = $conn->prepare("SELECT * FROM candidature WHERE user_id = ?");
$stmt_cand->bind_param("i", $user_id);
$stmt_cand->execute();
$cand = $stmt_cand->get_result()->fetch_assoc();

if (!$cand || $cand['statut'] !== 'soumise') {
    die("Dossier non encore soumis.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Récépissé de dépôt - <?= htmlspecialchars($_SESSION['user_nom']) ?></title>
    <style>
        body { font-family: sans-serif; padding: 40px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 30px; }
        .section { margin-bottom: 20px; }
        .label { font-weight: bold; width: 200px; display: inline-block; }
        .footer { margin-top: 50px; font-size: 0.8em; text-align: center; color: #666; }
        .stamp { border: 2px solid #27ae60; color: #27ae60; padding: 10px; display: inline-block; transform: rotate(-5deg); font-weight: bold; margin-top: 20px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #3498dbd2; color: white; border: none; cursor: pointer;">
            🖨️ Enregistrer en PDF / Imprimer
        </button>
        <a href="dashboard.php" style="margin-left: 10px;">Retour au tableau de bord</a>
    </div>

    <div class="header">
        <h1>RÉCÉPISSÉ DE DÉPÔT NUMÉRIQUE</h1>
        <p>Ministère de l'Intérieur - Portail Titre de Séjour</p>
    </div>

    <div class="section">
        <p><span class="label">Numéro de dossier :</span> #DS-<?= $user_id ?>-<?= date('Y') ?></p>
        <p><span class="label">Date de soumission :</span> <?= $cand['date_soumission'] ?></p>
        <p><span class="label">Statut :</span> <strong>DOSSIER TRANSMIS</strong></p>
    </div>

    <div class="section">
        <h3>Informations sur le demandeur</h3>
        <p><span class="label">Nom complet :</span> <?= htmlspecialchars($_SESSION['user_nom']) ?></p>
        <p><span class="label">Nationalité :</span> <?= htmlspecialchars($info['nationalite']) ?></p>
        <p><span class="label">N° Passeport :</span> <?= htmlspecialchars($info['num_passport']) ?></p>
        <p><span class="label">Type de demande :</span> <?= $info['renouvellement'] ? 'Renouvellement' : 'Première demande' ?></p>
    </div>

    <div class="stamp">DOSSIER REÇU LE <?= date('d/m/Y') ?></div>

    <div class="footer">
        <p>Ce document fait office de preuve de dépôt de votre demande en ligne.<br>
        Toute fausse déclaration est passible de poursuites.</p>
    </div>
</body>
</html>