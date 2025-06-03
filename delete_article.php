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

$stmt = $pdo->prepare("DELETE FROM Articles WHERE id = ?");
$stmt->execute([$articleId]);

header("Location: admin.php");
exit;
