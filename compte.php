<?php
session_start();
require 'db.php'; // Connexion à la base de données

// Récupération de l'ID utilisateur via GET ou session
$userId = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['user_id'] ?? null;
$isOwnAccount = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId;

if (!$userId) {
    echo "Aucun compte spécifié.";
    exit;
}

// Récupération des infos utilisateur
$stmt = $pdo->prepare("SELECT * FROM Users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    echo "Utilisateur introuvable.";
    exit;
}

// Articles publiés
$stmt = $pdo->prepare("SELECT * FROM Articles WHERE auteur_id = ?");
$stmt->execute([$userId]);
$articles = $stmt->fetchAll();

// Articles achetés si compte propre
$purchasedArticles = [];
$invoices = [];

if ($isOwnAccount) {
    $stmt = $pdo->prepare("
        SELECT Articles.* FROM Articles 
        JOIN Cart ON Cart.article_id = Articles.id 
        JOIN Invoice ON Invoice.user_id = Cart.id 
        WHERE Cart.user_id = ?
    ");
    $stmt->execute([$userId]);
    $purchasedArticles = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM Invoice WHERE user_id = ?");
    $stmt->execute([$userId]);
    $invoices = $stmt->fetchAll();
}

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isOwnAccount) {
    if (!empty($_POST['email'])) {
        $stmt = $pdo->prepare("UPDATE User SET email = ? WHERE id = ?");
        $stmt->execute([$_POST['email'], $userId]);
        echo "Email mis à jour.<br>";
    }
    if (!empty($_POST['password'])) {
        $hashed = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE User SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $userId]);
        echo "Mot de passe mis à jour.<br>";
    }
    if (!empty($_POST['add_balance'])) {
        $amount = floatval($_POST['add_balance']);
        $stmt = $pdo->prepare("UPDATE User SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$amount, $userId]);
        echo "Solde mis à jour.<br>";
    }
    header("Location: account.php"); // Rafraîchir pour éviter les resoumissions
    exit;
}
?>

<h1>Compte de <?= htmlspecialchars($user['username']) ?></h1>
<p>Email : <?= htmlspecialchars($user['email']) ?></p>
<p>Solde : <?= number_format($user['solde'], 2) ?> €</p>
<img src="<?= htmlspecialchars($user['photo_profil']) ?>" width="100" alt="Photo de profil">

<h2>Articles publiés</h2>
<ul>
<?php foreach ($articles as $article): ?>
    <li><a href="detail.php?id=<?= $article['id'] ?>"><?= htmlspecialchars($article['nom']) ?></a></li>
<?php endforeach; ?>
</ul>

<?php if ($isOwnAccount): ?>
    <h2>Articles achetés</h2>
    <ul>
    <?php foreach ($purchasedArticles as $article): ?>
        <li><?= htmlspecialchars($article['name']) ?> (<?= number_format($article['price'], 2) ?> €)</li>
    <?php endforeach; ?>
    </ul>

    <h2>Vos factures</h2>
    <ul>
    <?php foreach ($invoices as $invoice): ?>
        <li>Facture #<?= $invoice['id'] ?> - <?= $invoice['total_amount'] ?> € - <?= $invoice['date'] ?></li>
    <?php endforeach; ?>
    </ul>

    <h2>Modifier vos informations</h2>
    <form method="POST">
        <label>Nouvel email : <input type="email" name="email"></label><br>
        <label>Nouveau mot de passe : <input type="password" name="password"></label><br>
        <label>Ajouter au solde (€) : <input type="number" step="0.01" name="add_balance"></label><br>
        <button type="submit">Mettre à jour</button>
    </form>
<?php endif; ?>
