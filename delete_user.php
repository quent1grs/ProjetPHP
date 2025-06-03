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
    echo "ID utilisateur manquant.";
    exit;
}

$userId = intval($_GET['id']);

// Empêcher un admin de supprimer son propre compte par accident
if ($userId === $_SESSION['user_id']) {
    echo "Vous ne pouvez pas supprimer votre propre compte.";
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM Articles WHERE auteur_id = ?");
    $stmt->execute([$userId]);
    $articleIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($articleIds) {
        $inQuery = implode(',', array_fill(0, count($articleIds), '?'));

        // 2. Supprimer les entrées dans Cart
        $stmt = $pdo->prepare("DELETE FROM Cart WHERE article_id IN ($inQuery)");
        $stmt->execute($articleIds);

        // 3. Supprimer les entrées dans Stock
        $stmt = $pdo->prepare("DELETE FROM Stock WHERE article_id IN ($inQuery)");
        $stmt->execute($articleIds);

        // 4. Supprimer les articles eux-mêmes
        $stmt = $pdo->prepare("DELETE FROM Articles WHERE auteur_id = ?");
        $stmt->execute([$userId]);
    }

    // 5. Supprimer les factures liées à cet utilisateur
    $stmt = $pdo->prepare("DELETE FROM Invoice WHERE user_id = ?");
    $stmt->execute([$userId]);

    // 6. Supprimer le compte utilisateur
    $stmt = $pdo->prepare("DELETE FROM Users WHERE id = ?");
    $stmt->execute([$userId]);

// Suppression utilisateur
$stmt = $pdo->prepare("DELETE FROM Users WHERE id = ?");
$stmt->execute([$userId]);

header("Location: admin.php");
exit;
