<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// 1. On récupère le type de demande (Renouvellement ou non) depuis la table informations
$stmt_check = $conn->prepare("SELECT renouvellement FROM informations WHERE user_id = ?");
$stmt_check->bind_param("i", $user_id);
$stmt_check->execute();
$res_type = $stmt_check->get_result()->fetch_assoc();

// Si l'utilisateur n'a pas encore fait de choix, on le renvoie à la page de choix par sécurité
if (!$res_type) {
    header("Location: choix_demande.php");
    exit;
}

$is_renouvellement = ($res_type['renouvellement'] == 1);

if (isset($_POST['suivant'])) {

    $allowed_types = ['application/pdf'];
    $max_size = 5 * 1024 * 1024; // 5Mo
    
    // Configuration dynamique des obligations
    $uploads_config = [
        'passeport' => true, 
        'carte_sejour' => $is_renouvellement, // Obligatoire seulement si c'est un renouvellement
        'extrait_naissance' => true, 
        'casier_judiciaire' => true, 
        'certificat_medical' => true
    ];
    
    $files_dest = [
        'passeport' => null,
        'carte_sejour' => null,
        'extrait_naissance' => null,
        'casier_judiciaire' => null,
        'certificat_medical' => null
    ];

    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    foreach ($uploads_config as $field => $is_required) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            // Vérification taille et type
            if ($_FILES[$field]['size'] > $max_size) { 
                $message = "Le fichier $field dépasse 5MB"; 
                break; 
            }
            if (!in_array($_FILES[$field]['type'], $allowed_types)) { 
                $message = "Le fichier $field doit être un PDF"; 
                break; 
            }

            // Nommage unique pour éviter les conflits
            $filename = $user_id . "_" . time() . "_" . $field . ".pdf";
            $filepath = $target_dir . $filename;

            if (move_uploaded_file($_FILES[$field]['tmp_name'], $filepath)) {
                $files_dest[$field] = $filename;
            } else { 
                $message = "Erreur lors de l'upload de $field"; 
                break; 
            }
        } elseif ($is_required) {
            $message = "Veuillez télécharger le document obligatoire : " . str_replace('_', ' ', $field);
            break;
        }
    }

    if ($message == "") {
        // Mise à jour de la table documents (REPLACE permet de mettre à jour si l'ID existe déjà)
        $stmt = $conn->prepare("
            REPLACE INTO documents (user_id, passeport, carte_sejour, extrait_naissance, casier_judiciaire, certificat_medical)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "isssss",
            $user_id,
            $files_dest['passeport'],
            $files_dest['carte_sejour'],
            $files_dest['extrait_naissance'],
            $files_dest['casier_judiciaire'],
            $files_dest['certificat_medical']
        );
        $stmt->execute();

        header("Location: verification.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Téléchargement des Documents</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .required-star { color: #e74c3c; font-weight: bold; }
        .optional { color: #7f8c8d; font-size: 0.85em; font-style: italic; }
        .form-group { margin-bottom: 1.5rem; text-align: left; }
    </style>
</head>
<body>
<div class="container" style="max-width: 600px;">
    <h2>📂 Étape 2 : Vos Documents</h2>
    <p>Type de dossier : <strong><?= $is_renouvellement ? "🔄 Renouvellement" : "🆕 Premier dépôt" ?></strong></p>

    <?php if($message != ""): ?>
        <div class="message error"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
            <label>Passeport <span class="required-star">*</span></label>
            <input type="file" name="passeport" accept=".pdf" required>
        </div>

        <div class="form-group">
            <label>Ancienne Carte de séjour 
                <?= $is_renouvellement ? '<span class="required-star">*</span>' : '<span class="optional">(Non requis pour un premier dépôt)</span>' ?>
            </label>
            <input type="file" name="carte_sejour" accept=".pdf" <?= $is_renouvellement ? 'required' : '' ?>>
        </div>

        <div class="form-group">
            <label>Extrait de naissance <span class="required-star">*</span></label>
            <input type="file" name="extrait_naissance" accept=".pdf" required>
        </div>

        <div class="form-group">
            <label>Casier judiciaire <span class="required-star">*</span></label>
            <input type="file" name="casier_judiciaire" accept=".pdf" required>
        </div>

        <div class="form-group">
            <label>Certificat médical <span class="required-star">*</span></label>
            <input type="file" name="certificat_medical" accept=".pdf" required>
        </div>

        <button type="submit" name="suivant">Continuer vers la vérification →</button>
        <p><a href="dashboard.php" style="color: #7f8c8d;">Retour</a></p>
    </form>
</div>
</body>
</html>