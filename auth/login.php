<?php
session_start();
include("../config/db.php");

$message = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Utilisation d'une requête préparée pour la sécurité
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 1) {
        $user = $res->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];

            header("Location: ../user/dashboard.php");
            exit;
        } else {
            $message = "Mot de passe incorrect";
        }
    } else {
        $message = "Email introuvable";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Portail Séjour</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="container">
    <h2>Connexion</h2>

    <?php if ($message): ?>
        <div class="message error"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Adresse Email</label>
            <input type="email" name="email" placeholder="exemple@mail.com" required>
        </div>
        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="password" placeholder="Votre mot de passe" required>
        </div>
        <button type="submit" name="login" class="btn-primary">Se connecter</button>
    </form>

    <p>Pas encore inscrit ? <a href="register.php">Créer un compte</a></p>
    <p><a href="../index.php">Retour à l'accueil</a></p>
</div>

</body>
</html>