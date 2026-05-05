<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// 1. Pré-remplissage des données existantes
$stmt = $conn->prepare("SELECT * FROM informations WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$info = $stmt->get_result()->fetch_assoc();

if (isset($_POST['suivant'])) {
    $date_naissance = $_POST['date_naissance'];
    $nationalite = trim($_POST['nationalite']);
    $num_passport = trim($_POST['num_passport']);
    $date_delivrance = $_POST['date_delivrance'];
    $date_expiration = $_POST['date_expiration'];
    $situation_familiale = trim($_POST['situation_familiale']);
    $nom_pere = trim($_POST['nom_pere']);
    $nom_mere = trim($_POST['nom_mere']);

    if (empty($date_naissance) || empty($nationalite) || empty($num_passport)) {
        $message = "Veuillez remplir au moins les informations de base.";
    } else {
        // UPDATE ou INSERT automatique grâce à l'ID existant
        $stmt = $conn->prepare("
            REPLACE INTO informations 
            (user_id, date_naissance, nationalite, num_passport, date_delivrance, date_expiration, situation_familiale, nom_pere, nom_mere, renouvellement)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // On garde la valeur actuelle de renouvellement pour ne pas l'écraser par 0
        $renouv = $info['renouvellement'] ?? 0;

        $stmt->bind_param("issssssssi", 
            $user_id, $date_naissance, $nationalite, $num_passport, 
            $date_delivrance, $date_expiration, $situation_familiale, 
            $nom_pere, $nom_mere, $renouv
        );

        if ($stmt->execute()) {
            header("Location: documents.php");
            exit;
        } else {
            $message = "Erreur lors de l'enregistrement.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Informations Personnelles</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container" style="max-width: 600px;">
    <h2>📝 Étape 1 : Informations personnelles</h2>
    
    <?php if ($message): ?>
        <div class="message error"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Date de naissance</label>
        <input type="date" name="date_naissance" value="<?= $info['date_naissance'] ?? '' ?>" required>

        <label>Nationalité</label>
        <input type="text" name="nationalite" placeholder="Ex: Sénégalaise" value="<?= $info['nationalite'] ?? '' ?>" required>

        <label>Numéro de passeport</label>
        <input type="text" name="num_passport" value="<?= $info['num_passport'] ?? '' ?>" required>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <div>
                <label>Délivré le</label>
                <input type="date" name="date_delivrance" value="<?= $info['date_delivrance'] ?? '' ?>">
            </div>
            <div>
                <label>Expire le</label>
                <input type="date" name="date_expiration" value="<?= $info['date_expiration'] ?? '' ?>">
            </div>
        </div>

        <label>Situation familiale</label>
        <select name="situation_familiale" style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ddd;">
            <option value="Célibataire" <?= (@$info['situation_familiale'] == 'Célibataire') ? 'selected' : '' ?>>Célibataire</option>
            <option value="Marié(e)" <?= (@$info['situation_familiale'] == 'Marié(e)') ? 'selected' : '' ?>>Marié(e)</option>
        </select>

        <label>Nom du père</label>
        <input type="text" name="nom_pere" value="<?= $info['nom_pere'] ?? '' ?>">

        <label>Nom de la mère</label>
        <input type="text" name="nom_mere" value="<?= $info['nom_mere'] ?? '' ?>">

        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <a href="dashboard.php" style="flex: 1; text-align: center; padding: 12px; background: #95a5a6; color: white; text-decoration: none; border-radius: 5px;">Annuler</a>
            <button type="submit" name="suivant" style="flex: 2; margin: 0;">Enregistrer & Continuer →</button>
        </div>
    </form>
</div>
</body>
</html>
        <input type="hidden" name="renouvellement" value="<?= @$info['renouvellement'] ?>">

        <button type="submit" name="suivant">Suivant →</button>
    </form>
</div>
</body>
</html>
