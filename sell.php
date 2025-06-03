<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'ecommerce');
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit;
}

$message = "";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $publish_date = $_POST['publish_date'];
    $image_link = trim($_POST['image_link']);
    $author_id = $_SESSION['user_id'];

    if ($name && $description && $price > 0 && $publish_date && $image_link) {
        $stmt = $conn->prepare("INSERT INTO articles (nom, description, prix, date_publication, auteur_id, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsss", $name, $description, $price, $publish_date, $author_id, $image_link);

        if ($stmt->execute()) {
            $message = "Article ajouté avec succès.";
        } else {
            $message = "Erreur lors de l'ajout de l'article : " . $stmt->error;
        }
    } else {
        $message = "Veuillez remplir tous les champs correctement.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un article</title>
</head>
<body>
    <h1>Ajouter un nouvel article</h1>

    <?php if ($message): ?>
        <p style="color:green"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="name">Nom de l'article :</label><br>
        <input type="text" name="name" id="name" required><br><br>

        <label for="description">Description :</label><br>
        <textarea name="description" id="description" required></textarea><br><br>

        <label for="price">Prix (€) :</label><br>
        <input type="number" name="price" id="price" step="0.01" min="0" required><br><br>

        <label for="publish_date">Date de publication :</label><br>
        <input type="date" name="publish_date" id="publish_date" required><br><br>

        <label for="image_link">Lien de l'image :</label><br>
        <input type="text" name="image_link" id="image_link" required><br><br>

        <button type="submit">Ajouter l'article</button>
    </form>

    <p><a href="home.php">← Retour à l'accueil</a></p>
</body>
</html>
