<?php
session_start();
$conn = new mysqli("localhost", "root", "", "ecommerce");
if ($conn->connect_error) die("Erreur : " . $conn->connect_error);
if (!isset($_SESSION['user_id'])) header("Location: connexion.php");

$user_id = $_SESSION['user_id'];

$sql = "SELECT a.*, c.quantite FROM cart c 
        JOIN articles a ON c.article_id = a.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$articles = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $articles[] = $row;
    $total += $row['prix'] * $row['quantite'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer'])) {
    $adresse = $_POST['adresse'];
    $ville = $_POST['ville'];
    $cp = $_POST['code_postal'];

    $res = $conn->query("SELECT solde FROM users WHERE id = $user_id");
    $solde = $res->fetch_assoc()['solde'];

    if ($solde >= $total) {
$stmt = $conn->prepare("INSERT INTO invoice (user_id, date_transaction, montant, adresse_facturation, ville_facturation, code_postal) VALUES (?, CURDATE(), ?, ?, ?, ?)");
$stmt->bind_param("idsss", $user_id, $total, $adresse, $ville, $cp);
$stmt->execute();
$invoice_id = $conn->insert_id;

$stmt = $conn->prepare("UPDATE users SET solde = solde - ? WHERE id = ?");
$stmt->bind_param("di", $total, $user_id);
$stmt->execute();

$conn->query("DELETE FROM cart WHERE user_id = $user_id");

header("Location: facture.php?id=$invoice_id");
exit;

    } else {
        $message = "Solde insuffisant.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Validation de commande</title>
</head>
<body>
    <?php include 'header.php'; ?>

    <h1>Validation de la commande</h1>

    <?php if (!empty($message)) echo "<p style='color: red;'>$message</p>"; ?>

    <h3>Articles :</h3>
    <ul>
        <?php foreach ($articles as $a): ?>
            <li><?= htmlspecialchars($a['nom']) ?> (x<?= $a['quantite'] ?>) : <?= number_format($a['prix'] * $a['quantite'], 2) ?> €</li>
        <?php endforeach; ?>
    </ul>
    <p><strong>Total : <?= number_format($total, 2) ?> €</strong></p>

    <form method="post">
        <p>Adresse : <input type="text" name="adresse" required></p>
        <p>Ville : <input type="text" name="ville" required></p>
        <p>Code Postal : <input type="text" name="code_postal" required></p>
        <button type="submit" name="confirmer">Confirmer la commande</button>
    </form>

    <p><a href="cart.php">← Retour au panier</a></p>
</body>
</html>
