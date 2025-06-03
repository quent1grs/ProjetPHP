<?php
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'ecommerce';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo "Erreur de connexion.";
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Vous devez être connecté.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $article_id = intval($data['article_id']);
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO cart (user_id, article_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $article_id);

    if ($stmt->execute()) {
        echo "Article ajouté au panier.";
    } else {
        http_response_code(500);
        echo "Erreur lors de l'ajout.";
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo "Méthode non autorisée.";
}

$conn->close();
?>
