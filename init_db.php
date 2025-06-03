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
    ('alice', '$hashed_password', 'alice@example.com', 100.00, 'https://imgs.search.brave.com/hV7ggLrjUHFsJi4noN9y8Y-sXoBEH9ioch95Lq64ycQ/rs:fit:500:0:0:0/g:ce/aHR0cHM6Ly9mci53/ZWIuaW1nMi5hY3N0/YS5uZXQvY18zMDBf/MzAwL3BpY3R1cmVz/LzE0LzA2LzIwLzEz/LzAyLzAwMTc3Ni5q/cGc', 'client')";
$conn->query($sql);
$user_id = $conn->insert_id;

$hashed_password = password_hash("motdepasse123", PASSWORD_BCRYPT);
$sql = "INSERT INTO users (username, password, email, solde, photo_profil, role) VALUES 
    ('Toutou44', '$hashed_password', 'basto@example.com', 100.00, 'https://imgs.search.brave.com/Oxq_C4Ga5OvfzcK1XYRodqIEwamyfHDk2DNNXm9mulQ/rs:fit:500:0:0:0/g:ce/aHR0cHM6Ly9tZWRp/YS5nZXR0eWltYWdl/cy5jb20vaWQvOTQ3/NDA1OTQvZnIvcGhv/dG8vaW50cmlndSVD/MyVBOS5qcGc_cz02/MTJ4NjEyJnc9MCZr/PTIwJmM9cHp2b1Qz/X3VLcEFMa2hXbTE4/czQ5NnVTTVJoaUM3/bXVNV18wMFZ1SXRj/TT0', 'client')";
$conn->query($sql);

$user_id = $conn->insert_id;
$hashed_password = password_hash("motdepasse123", PASSWORD_BCRYPT);
$sql = "INSERT INTO users (username, password, email, solde, photo_profil, role) VALUES 
    ('Karim Benzouzouz', '$hashed_password', 'karim@example.com', 100.00, 'https://imgs.search.brave.com/oq-k-lkK8A83vxdlYbgHXE2HFhEnUp0cHSbTIuc4_x4/rs:fit:500:0:0:0/g:ce/aHR0cHM6Ly93YWxs/cGFwZXJjYXQuY29t/L3cvZnVsbC82LzIv/ZC8xMjQ5NS0xMDgw/eDE5MjAtbW9iaWxl/LTEwODBwLWNyaXN0/aWFuby1yb25hbGRv/LXdhbGxwYXBlci5q/cGc', 'client')";
$conn->query($sql);
$user_id = $conn->insert_id;

$user_id = $conn->insert_id;
$hashed_password = password_hash("tt", PASSWORD_BCRYPT);
$sql = "INSERT INTO users (username, password, email, solde, photo_profil, role) VALUES 
    ('tt', '$hashed_password', 'karim@example.com', 100.00, 'https://imgs.search.brave.com/TFGbDyhm8ij9IodVPI_ydCak6LTZ9AdsNBrkYgEFblQ/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly93d3cu/aW5zdGl0dXRsZWpl/dW5lLm9yZy93cC1j/b250ZW50L3VwbG9h/ZHMvMjAxNy8xMC90/cmlzb21pZV8yMS5q/cGc', 'client')";
$conn->query($sql);
$user_id = $conn->insert_id;

// Insertion d’un article
$sql = "INSERT INTO articles (nom, description, prix, date_publication, auteur_id, image_url) VALUES 
    ('Carte de Jean-Yves', 'une carte hein', 49.99, CURDATE(), $user_id, 'https://img.freepik.com/vecteurs-libre/credit-carte-debit-mockup_1017-6276.jpg?ga=GA1.1.332348959.1748942902&semt=ais_hybrid&w=740')";
$conn->query($sql);
$article_id = $conn->insert_id;
$sql = "INSERT INTO articles (nom, description, prix, date_publication, auteur_id, image_url) VALUES 
    ('Carte de Gilebert le tigre', 'une autre carte', 49.99, CURDATE(), $user_id, 'https://img.freepik.com/vecteurs-libre/conception-carte-credit-realiste_23-2149126090.jpg?ga=GA1.1.332348959.1748942902&semt=ais_hybrid&w=740')";
$conn->query($sql);
$article_id = $conn->insert_id;
$sql = "INSERT INTO articles (nom, description, prix, date_publication, auteur_id, image_url) VALUES 
    ('Carte de Mireille', 'une autre carte tu te doutes bien', 49.99, CURDATE(), $user_id, 'https://img.freepik.com/vecteurs-libre/carte-credit-monochrome-realiste_52683-74366.jpg?ga=GA1.1.332348959.1748942902&semt=ais_hybrid&w=740')";
$conn->query($sql);
$article_id = $conn->insert_id;
$sql = "INSERT INTO articles (nom, description, prix, date_publication, auteur_id, image_url) VALUES 
    ('Carte du Abdoul Karaba', 'Et encore une autre', 149.99, CURDATE(), $user_id, 'https://img.freepik.com/vecteurs-libre/modele-carte-credit-style-neumorphisme_1017-30676.jpg?ga=GA1.1.332348959.1748942902&semt=ais_hybrid&w=740')";
$conn->query($sql);
$article_id = $conn->insert_id;
$sql = "INSERT INTO articles (nom, description, prix, date_publication, auteur_id, image_url) VALUES 
    ('Carte de Nekfeu', 'Et re une autre', 49.99, CURDATE(), $user_id, 'https://img.freepik.com/vecteurs-libre/icone-carte-credit-illustration-isole-blanc_1284-47653.jpg?ga=GA1.1.332348959.1748942902&semt=ais_hybrid&w=740')";
$conn->query($sql);
$article_id = $conn->insert_id;
$sql = "INSERT INTO articles (nom, description, prix, date_publication, auteur_id, image_url) VALUES 
    ('Carte de Pre Malone', 'Devines', 49.99, CURDATE(), $user_id, 'https://img.freepik.com/vecteurs-libre/carte-credit-monochrome-realiste_52683-74365.jpg?ga=GA1.1.332348959.1748942902&semt=ais_hybrid&w=740')";
$conn->query($sql);

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
