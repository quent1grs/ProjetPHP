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
    $publish_date = date('Y-m-d'); // date actuelle
    $author_id = $_SESSION['user_id'];

    // Vérifie que l'image est envoyée
    if ($name && $description && $price > 0 && isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $imageTmpName = $_FILES['image_file']['tmp_name'];
        $imageName = uniqid() . '_' . basename($_FILES['image_file']['name']);
        $imagePath = $uploadDir . $imageName;

        // Déplace l'image
        if (move_uploaded_file($imageTmpName, $imagePath)) {
            $stmt = $conn->prepare("INSERT INTO articles (nom, description, prix, date_publication, auteur_id, image_url) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdsss", $name, $description, $price, $publish_date, $author_id, $imagePath);

            if ($stmt->execute()) {
                $message = "Article ajouté avec succès.";
            } else {
                $message = "Erreur lors de l'ajout de l'article : " . $stmt->error;
            }
        } else {
            $message = "Erreur lors du téléchargement de l'image.";
        }
    } else {
        $message = "Veuillez remplir tous les champs correctement et choisir une image.";
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
    <?php include 'header.php'; ?>
    <h1>Ajouter un nouvel article</h1>

    <?php if ($message): ?>
        <p style="color:green"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="name">Nom de l'article :</label><br>
        <input type="text" name="name" id="name" required><br><br>

        <label for="description">Description :</label><br>
        <textarea name="description" id="description" required></textarea><br><br>

        <label for="price">Prix (€) :</label><br>
        <input type="number" name="price" id="price" step="0.01" min="0" required><br><br>

        <!-- Date supprimée -->

        <label for="image_file">Image du produit :</label><br>
        <input type="file" name="image_file" id="image_file" accept="image/*" required><br><br>

        <button type="submit">Ajouter l'article</button>
    </form>


    <p><a href="home.php">← Retour à l'accueil</a></p>
</body>
</html>
