<?php
session_start();

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'ecommerce';
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur connexion : " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Mettre à jour les quantités si besoin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        foreach ($_POST['quantite'] as $article_id => $qte) {
            $qte = max(1, intval($qte)); // pas de valeur < 1
            $stmt = $conn->prepare("UPDATE cart SET quantite = ? WHERE user_id = ? AND article_id = ?");
            $stmt->bind_param("iii", $qte, $user_id, $article_id);
            $stmt->execute();
        }
    }

    if (isset($_POST['delete'])) {
        $article_id = intval($_POST['delete']);
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND article_id = ?");
        $stmt->bind_param("ii", $user_id, $article_id);
        $stmt->execute();
    }

    if (isset($_POST['commander'])) {
        // On récupère le solde de l'utilisateur
        $res = $conn->query("SELECT solde FROM users WHERE id = $user_id");
        $solde = $res->fetch_assoc()['solde'];

        // Récupération des articles du panier
        $sql = "SELECT a.id, a.prix, c.quantite FROM cart c 
                JOIN articles a ON c.article_id = a.id 
                WHERE c.user_id = $user_id";
        $res = $conn->query($sql);

        $total = 0;
        $articles = [];
        while ($row = $res->fetch_assoc()) {
            $total += $row['prix'] * $row['quantite'];
            $articles[] = $row;
        }

        if ($solde >= $total) {
            // Créer une facture
            $stmt = $conn->prepare("INSERT INTO invoice (user_id, date_transaction, montant, adresse_facturation, ville_facturation, code_postal) VALUES (?, CURDATE(), ?, 'Adresse', 'Ville', '00000')");
            $stmt->bind_param("id", $user_id, $total);
            $stmt->execute();
            $invoice_id = $conn->insert_id;

            // Déduire le solde
            $stmt = $conn->prepare("UPDATE users SET solde = solde - ? WHERE id = ?");
            $stmt->bind_param("di", $total, $user_id);
            $stmt->execute();

            // Vider le panier
            $conn->query("DELETE FROM cart WHERE user_id = $user_id");

            $message = "Commande effectuée avec succès ! <a href='facture.php?id=$invoice_id' target='_blank'>Télécharger la facture</a>";
        } else {
            $message = "Solde insuffisant.";
        }
    }
}

// Vérifie si la colonne quantite existe déjà
$colCheck = $conn->query("SHOW COLUMNS FROM cart LIKE 'quantite'");
if ($colCheck->num_rows == 0) {
    $conn->query("ALTER TABLE cart ADD COLUMN quantite INT DEFAULT 1");
}


$sql = "SELECT a.*, c.quantite FROM cart c 
        JOIN articles a ON c.article_id = a.id 
        WHERE c.user_id = $user_id";
$result = $conn->query($sql);

$total = 0;
$articles = [];
while ($row = $result->fetch_assoc()) {
    $articles[] = $row;
    $total += $row['prix'] * $row['quantite'];
}

// Récupérer solde
$res = $conn->query("SELECT solde FROM users WHERE id = $user_id");
$solde = $res->fetch_assoc()['solde'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Panier</title>
</head>
<body>
    <?php include 'header.php'; ?>
    <h1>Mon Panier</h1>

    <?php if (isset($message)) echo "<p style='color: red;'>$message</p>"; ?>

    <?php if (empty($articles)): ?>
        <p>Votre panier est vide.</p>
    <?php else: ?>
        <form method="post">
            <table border="1" cellpadding="8">
                <tr>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($articles as $article): ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($article['image_url']) ?>" width="50"></td>
                        <td><?= htmlspecialchars($article['nom']) ?></td>
                        <td><?= number_format($article['prix'], 2) ?> €</td>
                        <td>
                            <input type="number" name="quantite[<?= $article['id'] ?>]" value="<?= $article['quantite'] ?>" min="1">
                        </td>
                        <td><?= number_format($article['prix'] * $article['quantite'], 2) ?> €</td>
                        <td>
                            <button type="submit" name="delete" value="<?= $article['id'] ?>">Supprimer</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <p><strong>Total : <?= number_format($total, 2) ?> €</strong></p>
            <p><strong>Votre solde : <?= number_format($solde, 2) ?> €</strong></p>

            <button type="submit" name="update">Mettre à jour le panier</button>
            <a href="validate.php" style="display:inline-block; padding:10px 15px; background:#4CAF50; color:white; text-decoration:none;">Passer commande</a>
        </form>
    <?php endif; ?>

    <p><a href="home.php">← Retour à l'accueil</a></p>
</body>
</html>
