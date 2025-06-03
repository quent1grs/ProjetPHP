<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT role FROM Users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['role'] !== 'admin') {
    echo "⛔ Accès refusé.";
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID article manquant.";
    exit;
}

$articleId = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM Articles WHERE id = ?");
$stmt->execute([$articleId]);
$article = $stmt->fetch();

if (!$article) {
    echo "Article introuvable.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $price = floatval($_POST['price'] ?? 0);

    if (empty($nom)) {
        echo "Le nom est obligatoire.";
    } elseif ($price <= 0) {
        echo "Le prix doit être supérieur à 0.";
    } else {
        $stmt = $pdo->prepare("UPDATE Articles SET nom = ?, prix = ? WHERE id = ?");
        $stmt->execute([$nom, $price, $articleId]);
        echo "Article mis à jour.<br>";
        $article['nom'] = $nom;
        $article['price'] = $price;
    }
}
?>

<h1>Modifier article #<?= $article['id'] ?></h1>
<form method="POST">
    <label>Nom : <input type="text" name="nom" value="<?= htmlspecialchars($article['nom']) ?>" required></label><br><br>
    <label>Prix (€) : <input type="number" step="0.01" name="price" value="<?= number_format((float)($article['price'] ?? 0), 2) ?>" required></label><br><br>
    <button type="submit">Mettre à jour</button>
</form>
<a href="admin.php">← Retour à l'administration</a>
