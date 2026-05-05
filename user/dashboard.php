<?php
session_start();
include("../config/db.php");

// 1. Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_nom = $_SESSION['user_nom'] ?? "Utilisateur";

// 2. Récupération des informations de dossier (Type et Statut)
// On cherche si l'utilisateur a déjà choisi un type de demande
$stmt_info = $conn->prepare("SELECT renouvellement FROM informations WHERE user_id = ?");
$stmt_info->bind_param("i", $user_id);
$stmt_info->execute();
$info = $stmt_info->get_result()->fetch_assoc();

// On cherche le statut de la candidature
$stmt_cand = $conn->prepare("SELECT statut FROM candidature WHERE user_id = ?");
$stmt_cand->bind_param("i", $user_id);
$stmt_cand->execute();
$cand = $stmt_cand->get_result()->fetch_assoc();

$statut = $cand['statut'] ?? 'En attente';

// Détermination du badge de type
if (!isset($info)) {
    $type_badge = "<span class='badge warning'>Non défini ⚠️</span>";
    $type_label = "Veuillez choisir votre type de demande";
} else {
    $is_renouv = $info['renouvellement'] == 1;
    $type_badge = $is_renouv ? "<span class='badge success'>🔄 Renouvellement</span>" : "<span class='badge success'>🆕 Premier Titre</span>";
    $type_label = $is_renouv ? "Dossier de renouvellement" : "Dossier de première demande";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Espace - Tableau de bord</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .welcome-section { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; text-align: left; }
        .status-container { display: flex; align-items: center; gap: 15px; margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee; }
        
        .badge { padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 0.85em; }
        .badge.success { background: #d4edda; color: #155724; }
        .badge.warning { background: #fff3cd; color: #856404; }

        .nav-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .nav-card { 
            background: white; padding: 20px; border-radius: 10px; text-decoration: none; color: #333;
            border: 2px solid transparent; transition: all 0.3s ease; display: flex; flex-direction: column;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .nav-card:hover { border-color: #3498db; transform: translateY(-3px); box-shadow: 0 6px 12px rgba(0,0,0,0.1); }
        .nav-card h3 { margin: 0 0 10px 0; color: #2c3e50; display: flex; align-items: center; gap: 10px; }
        .nav-card p { margin: 0; color: #7f8c8d; font-size: 0.9em; }
        
        .btn-logout { background: #e74c3c; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-top: 30px; transition: 0.3s; }
        .btn-logout:hover { background: #c0392b; }
        
        .submitted-msg { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #17a2b8; }
    </style>
</head>
<body>

<div class="container" style="max-width: 900px; background: none; box-shadow: none;">
    
    <div class="welcome-section">
        <h2 style="margin: 0;">Bonjour, <?= htmlspecialchars($user_nom) ?> 👋</h2>
        <div class="status-container">
            <div><strong>Type :</strong> <?= $type_badge ?></div>
            <div><strong>Statut :</strong> <span class="badge" style="background: #e9ecef;"><?= htmlspecialchars($statut) ?></span></div>
        </div>
    </div>

    <?php if ($statut === 'soumise'): ?>
        <div class="submitted-msg">
            <strong>Dossier déposé !</strong> Votre demande est en cours d'examen. Vous pouvez télécharger votre reçu dans la section "Vérification".
        </div>
    <?php endif; ?>

    <div class="nav-grid">
        <a href="choix_demande.php" class="nav-card">
            <h3><span>01</span> Type de demande</h3>
            <p>Modifier votre choix : Premier titre ou Renouvellement.</p>
        </a>

        <a href="informations.php" class="nav-card">
            <h3><span>02</span> Mes Informations</h3>
            <p>Renseignez votre identité, passeport et situation familiale.</p>
        </a>

        <a href="documents.php" class="nav-card">
            <h3><span>03</span> Mes Documents</h3>
            <p>Téléversez vos justificatifs au format PDF (Passeport, etc.).</p>
        </a>

        <a href="verification.php" class="nav-card">
            <h3><span>04</span> Vérification</h3>
            <p>Relisez votre dossier et téléchargez votre récépissé final.</p>
        </a>
    </div>

    <form action="../auth/logout.php" method="POST">
        <button type="submit" class="btn-logout">Déconnexion sécurisée</button>
    </form>
</div>

</body>
</html>