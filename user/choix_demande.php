<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['choix'])) {
    $type = ($_POST['type'] == 'renouvellement') ? 1 : 0;

    // Mise à jour du choix dans la base de données
    $stmt = $conn->prepare("INSERT INTO informations (user_id, renouvellement) VALUES (?, ?) 
                            ON DUPLICATE KEY UPDATE renouvellement = VALUES(renouvellement)");
    $stmt->bind_param("ii", $user_id, $type);
    
    if ($stmt->execute()) {
        header("Location: informations.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Type de Demande</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .choice-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
        }
        .choice-card {
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: left;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .choice-card:hover {
            border-color: #3498db;
            background: #f7fbff;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .choice-card h3 {
            margin: 15px 0 10px 0;
            color: #2c3e50;
        }
        .choice-card p {
            font-size: 0.9em;
            color: #7f8c8d;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        .icon { font-size: 40px; }
        .btn-select {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
        }
        /* Style pour retirer le style par défaut des boutons */
        .card-button {
            background: none;
            border: none;
            padding: 0;
            width: 100%;
            text-align: inherit;
            font-family: inherit;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container" style="max-width: 800px;">
    <h2>Quelle est votre situation ?</h2>
    <p>Sélectionnez l'option qui correspond à votre demande actuelle pour adapter vos justificatifs.</p>
    
    <form method="POST" class="choice-container">
        <button type="submit" name="choix" class="card-button">
            <input type="hidden" name="type" value="premier">
            <div class="choice-card">
                <div>
                    <span class="icon">🆕</span>
                    <h3>Première Demande</h3>
                    <p>Je viens d'arriver en France ou je n'ai jamais eu de carte de séjour. Je souhaite effectuer mon premier dépôt de dossier.</p>
                </div>
                <div class="btn-select">Choisir cette option</div>
            </div>
        </button>

        <button type="submit" name="choix" class="card-button">
            <input type="hidden" name="type" value="renouvellement">
            <div class="choice-card">
                <div>
                    <span class="icon">🔄</span>
                    <h3>Renouvellement</h3>
                    <p>Je possède déjà un titre de séjour qui arrive bientôt à expiration. Je souhaite prolonger sa validité pour l'année prochaine.</p>
                </div>
                <div class="btn-select">Choisir cette option</div>
            </div>
        </button>
    </form>
    
    <div style="margin-top: 30px;">
        <a href="dashboard.php" style="color: #95a5a6; text-decoration: none;">🏠 Retour au tableau de bord</a>
    </div>
</div>
</body>
</html>