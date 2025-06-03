<?php
session_start();
require 'db.php';

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit;
}

// Vérification du rôle de l'utilisateur
$stmt = $pdo->prepare("SELECT role FROM Users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['role'] !== 'admin') {
    echo "⛔ Accès refusé. Réservé aux administrateurs.";
    exit;
}

// Récupération des utilisateurs et articles
$users = $pdo->query("SELECT * FROM Users")->fetchAll();
$articles = $pdo->query("SELECT * FROM Articles")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { padding: 10px; border: 1px solid #ccc; }
        th { background-color: #f2f2f2; }
        h2 { margin-top: 40px; }
    </style>
</head>
<body>

<h1>👨‍💼 Tableau d'administration</h1>

<h2>👥 Utilisateurs</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Nom d'utilisateur</th>
        <th>Email</th>
        <th>Rôle</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($users as $u): ?>
    <tr>
        <td><?= $u['id'] ?></td>
        <td><?= htmlspecialchars($u['username']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= $u['role'] ?></td>
        <td>
            <a href="edit_user.php?id=<?= $u['id'] ?>">✏️ Modifier</a> |
            <a href="delete_user.php?id=<?= $u['id'] ?>" onclick="return confirm('Supprimer cet utilisateur ?')">🗑️ Supprimer</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>🛒 Articles</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Nom</th>
        <th>Prix (€)</th>
        <th>Auteur ID</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($articles as $article): ?>
    <tr>
        <td><?= $article['id'] ?></td>
        <td><?= htmlspecialchars($article['nom']) ?></td>
        <td><?= number_format($article['prix'], 2) ?></td>
        <td><?= $article['auteur_id'] ?></td>
        <td>
            <a href="edit_article.php?id=<?= $article['id'] ?>">✏️ Modifier</a> |
            <a href="delete_article.php?id=<?= $article['id'] ?>" onclick="return confirm('Supprimer cet article ?')">🗑️ Supprimer</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
