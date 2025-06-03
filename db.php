<?php
// Paramètres de connexion
$host = 'localhost';       // Ou l'adresse IP du serveur MySQL
$dbname = 'ecommerce';     // Nom de votre base de données
$users = 'root';            // Nom d'utilisateur MySQL
$pass = '';                // Mot de passe MySQL (vide si pas défini)

// Options PDO
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Affiche les erreurs SQL
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Récupère les résultats sous forme de tableau associatif
    PDO::ATTR_EMULATE_PREPARES => false, // Prépare les requêtes nativement si possible
];

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $users, $pass, $options);
} catch (PDOException $e) {
    // En cas d'erreur
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
