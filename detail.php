<?php
session_start();

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'ecommerce';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    die("Aucun article spécifié.");
}

$article_id = intval($_GET['id']);
$sql = "SELECT a.*, u.username FROM articles a
        JOIN users u ON a.auteur_id = u.id
        WHERE a.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Article introuvable.");
}

$article = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($article['nom']) ?></title>
</head>
<body>
    <?php include 'header.php'; ?>
    <h1><?= htmlspecialchars($article['nom']) ?></h1>
    <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="<?= htmlspecialchars($article['nom']) ?>" width="200">
    <p><strong>Description :</strong> <?= nl2br(htmlspecialchars($article['description'])) ?></p>
    <p><strong>Prix :</strong> <?= htmlspecialchars($article['prix']) ?> €</p>
    <p><strong>Vendu par :</strong> <?= htmlspecialchars($article['username']) ?></p>
    <p><strong>Date de publication :</strong> <?= htmlspecialchars($article['date_publication']) ?></p>

    <?php if (isset($_SESSION['user_id'])): ?>
        <button onclick="ajouterAuPanier(<?= $article['id'] ?>)">Ajouter au panier</button>
        <div id="message" style="color: green;"></div>
    <?php else: ?>
        <p><a href="connexion.php">Connectez-vous</a> pour ajouter au panier.</p>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $article['auteur_id'] || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'))): ?>
        <form action="edit.php" method="POST">
            <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
            <button type="submit">Modifier l’annonce</button>
        </form>
    <?php endif; ?>



    <a href="home.php">← Retour</a>

    <script>
    function ajouterAuPanier(articleId) {
        fetch('ajouter_panier.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ article_id: articleId })
        })
        .then(response => response.text())
        .then(message => {
            document.getElementById('message').innerText = message;
        })
        .catch(error => {
            document.getElementById('message').innerText = "Erreur : " + error;
        });
    }
    </script>
</body>
</html>
