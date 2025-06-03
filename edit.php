<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Accès non autorisé. Veuillez vous connecter.");
}

$conn = new mysqli('localhost', 'root', '', 'ecommerce');
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Récupérer l'ID de l'article envoyé en POST depuis detail.php
if (!isset($_POST['article_id'])) {
    die("Article non spécifié.");
}

$article_id = intval($_POST['article_id']);

// Vérifier que l'utilisateur a le droit de modifier cet article
$sql = "SELECT * FROM articles WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Article introuvable.");
}

$article = $result->fetch_assoc();

if ($_SESSION['user_id'] != $article['auteur_id'] && $_SESSION['role'] !== 'admin') {
    die("Vous n'avez pas l'autorisation de modifier cet article.");
}

// Traitement de la modification ou suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'supprimer') {
        $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->bind_param("i", $article_id);
        $stmt->execute();
        echo "Article supprimé. <a href='home.php'>Retour à l'accueil</a>";
        exit;
    } elseif ($_POST['action'] === 'modifier') {
        $nom = $_POST['nom'];
        $description = $_POST['description'];
        $prix = $_POST['prix'];

        $stmt = $conn->prepare("UPDATE articles SET nom=?, description=?, prix=? WHERE id=?");
        $stmt->bind_param("ssdi", $nom, $description, $prix, $article_id);
        $stmt->execute();
        echo "Article modifié. <a href='detail.php?id=$article_id'>Retour à l'article</a>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier l'article</title>
</head>
<body>
    <h1>Modifier l'article</h1>
    <form method="POST">
        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">

        <label>Nom :</label><br>
        <input type="text" name="nom" value="<?= htmlspecialchars($article['nom']) ?>" required><br>

        <label>Description :</label><br>
        <textarea name="description" required><?= htmlspecialchars($article['description']) ?></textarea><br>

        <label>Prix (€) :</label><br>
        <input type="number" step="0.01" name="prix" value="<?= htmlspecialchars($article['prix']) ?>" required><br><br>

        <button type="submit" name="action" value="modifier">Enregistrer les modifications</button>
        <button type="submit" name="action" value="supprimer" onclick="return confirm('Supprimer cet article ?')">Supprimer</button>
    </form>
</body>
</html>
