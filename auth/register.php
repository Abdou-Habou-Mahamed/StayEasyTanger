<?php
session_start();
include("../config/db.php");

$message = "";

if (isset($_POST['register'])) {

    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Vérification des champs
    if ($nom === "" || $email === "" || $password === "") {
        $message = "Tous les champs sont obligatoires";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Adresse email invalide";
    } elseif (strlen($password) < 6) {
        $message = "Le mot de passe doit contenir au moins 6 caractères";
    } else {

        // Vérification si l'email existe déjà
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Cet email existe déjà";
        } else {

            // Hashage du mot de passe
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insertion utilisateur
            $stmt = $conn->prepare("INSERT INTO users (nom, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nom, $email, $password_hash);

            if ($stmt->execute()) {
                // Connexion automatique après inscription
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['user_nom'] = $nom;

               header("Location: ../user/choix_demande.php");
                exit;
            } else {
                $message = "Erreur lors de l'inscription, veuillez réessayer";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="container">
    <h2>Créer un compte</h2>

    <?php if ($message != ""): ?>
        <div class="message error"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="text" name="nom" placeholder="Nom complet" required>
        <input type="email" name="email" placeholder="Adresse email" required>
        <input type="password" name="password" placeholder="Mot de passe (min 6 caractères)" required>
        <button type="submit" name="register">S'inscrire</button>
    </form>

    <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
</div>

</body>
</html>
