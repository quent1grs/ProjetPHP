<?php
session_start();

// Connexion à la base de données
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'ecommerce';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Récupération des articles, les plus récents en premier
$sql = "SELECT * FROM articles ORDER BY date_publication DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Boutique</title>
</head>
<body>
    <?php include 'header.php'; ?>
    <h1>Bienvenue sur notre boutique en ligne</h1>
    <p>Nous sommes le <?php echo date("d/m/Y"); ?></p>

    <h2>Articles en vente :</h2>
    <?php if ($result->num_rows > 0): ?>
        <ul>
            <?php while ($row = $result->fetch_assoc()): ?>
                <li>
                    <a href="detail.php?id=<?= $row['id'] ?>">
                        <strong><?= htmlspecialchars($row['nom']) ?></strong>
                    </a><br>
                    Prix : <?= number_format($row['prix'], 2, ',', ' ') ?> €<br>
                    <img src="<?= $row['image_url'] ?>" alt="<?= htmlspecialchars($row['nom']) ?>" width="150"><br><br>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>Aucun article en vente pour le moment.</p>
    <?php endif; ?>

    <?php $conn->close(); ?>
</body>
</html>