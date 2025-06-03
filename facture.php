<?php
session_start();
$conn = new mysqli("localhost", "root", "", "ecommerce");
if ($conn->connect_error) die("Erreur : " . $conn->connect_error);

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID de facture manquant.";
    exit;
}

$invoice_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Récupérer la facture
$stmt = $conn->prepare("SELECT * FROM invoice WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $invoice_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "Facture introuvable.";
    exit;
}
$facture = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture #<?= $facture['id'] ?></title>
</head>
<body>
    <h1>Facture #<?= $facture['id'] ?></h1>
    <p>Date : <?= $facture['date_transaction'] ?></p>
    <p>Client ID : <?= $facture['user_id'] ?></p>
    <p>Adresse : <?= htmlspecialchars($facture['adresse_facturation']) ?></p>
    <p>Ville : <?= htmlspecialchars($facture['ville_facturation']) ?></p>
    <p>Code Postal : <?= htmlspecialchars($facture['code_postal']) ?></p>
    <hr>
    <h3>Montant total : <?= number_format($facture['montant'], 2) ?> €</h3>
</body>
</html>
