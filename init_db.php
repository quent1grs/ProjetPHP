<?php

// Fichier d'initialisation de la bdd
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'ecommerce';

// Connexion au serveur MySQL
$conn = new mysqli($host, $user, $password);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Création de la base de données
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Base de données créée ou déjà existante.<br>";
} else {
    die("Erreur création BDD : " . $conn->error);
}

$conn->select_db($dbname);

// Création de la table Users
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    solde DECIMAL(10,2) DEFAULT 0,
    photo_profil TEXT,
    role VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Création de la table Articles
$sql = "CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    description TEXT,
    prix DECIMAL(10,2),
    date_publication DATE,
    auteur_id INT,
    image_url TEXT,
    FOREIGN KEY (auteur_id) REFERENCES users(id)
)";
$conn->query($sql);

// Création de la table Carts
$sql = "CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    article_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (article_id) REFERENCES articles(id)
)";
$conn->query($sql);

// Création de la table Stock
$sql = "CREATE TABLE IF NOT EXISTS stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT,
    quantite INT,
    FOREIGN KEY (article_id) REFERENCES articles(id)
)";
$conn->query($sql);

// Création de la table Invoices
$sql = "CREATE TABLE IF NOT EXISTS invoice (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    date_transaction DATE,
    montant DECIMAL(10,2),
    adresse_facturation TEXT,
    ville_facturation VARCHAR(100),
    code_postal VARCHAR(10),
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
$conn->query($sql);

// Insertion d'un utilisateur
$hashed_password = password_hash("motdepasse123", PASSWORD_BCRYPT);
$sql = "INSERT INTO users (username, password, email, solde, photo_profil, role) VALUES 
    ('alice', '$hashed_password', 'alice@example.com', 100.00, 'images/alice.jpg', 'client')";
$conn->query($sql);
$user_id = $conn->insert_id;

// Insertion d’un article
$sql = "INSERT INTO articles (nom, description, prix, date_publication, auteur_id, image_url) VALUES 
    ('Produit Test', 'Description du produit test', 49.99, CURDATE(), $user_id, 'images/produit.jpg')";
$conn->query($sql);
$article_id = $conn->insert_id;

// Insertion dans le panier
$sql = "INSERT INTO cart (user_id, article_id) VALUES ($user_id, $article_id)";
$conn->query($sql);

// Insertion dans le stock
$sql = "INSERT INTO stock (article_id, quantite) VALUES ($article_id, 20)";
$conn->query($sql);

// Insertion d'une facture
$sql = "INSERT INTO invoice (user_id, date_transaction, montant, adresse_facturation, ville_facturation, code_postal) VALUES 
    ($user_id, CURDATE(), 49.99, '123 Rue de Paris', 'Paris', '75001')";
$conn->query($sql);

echo "<br>Initialisation terminée avec succès.";

$conn->close();
?>
