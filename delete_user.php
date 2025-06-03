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

// Suppression utilisateur
$stmt = $pdo->prepare("DELETE FROM Users WHERE id = ?");
$stmt->execute([$userId]);

header("Location: admin.php");
exit;
