<?php
// Paramètres de connexion
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "sejour";

// Création de la connexion
$conn = new mysqli($host, $user, $pass, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    // On affiche un message d'erreur clair
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Forcer l'encodage UTF-8 pour les accents (très important pour les noms étrangers)
$conn->set_charset("utf8mb4");
?>