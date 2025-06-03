<?php
session_start();
require 'db.php'; // Connexion à la base de données

// Récupération de l'ID utilisateur via GET ou session
$userId = isset($_GET['id']) ? intval($_GET['id']) : ($_SESSION['user_id'] ?? null);
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
    // Récupérer les articles achetés via la table Cart
    $stmt = $pdo->prepare("
        SELECT Articles.* FROM Articles
        JOIN Cart ON Cart.article_id = Articles.id
        WHERE Cart.user_id = ?
    ");
    $stmt->execute([$userId]);
    $purchasedArticles = $stmt->fetchAll();

    // Récupérer les factures
    $stmt = $pdo->prepare("SELECT * FROM Invoice WHERE user_id = ?");
    $stmt->execute([$userId]);
    $invoices = $stmt->fetchAll();
}

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isOwnAccount) {
    // Suppression du compte
    if (isset($_POST['delete_account'])) {
        $stmt = $pdo->prepare("SELECT id FROM Articles WHERE auteur_id = ?");
        $stmt->execute([$userId]);
        $articleIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if ($articleIds) {
            $inQuery = implode(',', array_fill(0, count($articleIds), '?'));
            $stmt = $pdo->prepare("DELETE FROM Cart WHERE article_id IN ($inQuery)");
            $stmt->execute($articleIds);

            $stmt = $pdo->prepare("DELETE FROM Stock WHERE article_id IN ($inQuery)");
            $stmt->execute($articleIds);

            $stmt = $pdo->prepare("DELETE FROM Articles WHERE auteur_id = ?");
            $stmt->execute([$userId]);
        }

        $stmt = $pdo->prepare("DELETE FROM Invoice WHERE user_id = ?");
        $stmt->execute([$userId]);

        $stmt = $pdo->prepare("DELETE FROM Users WHERE id = ?");
        $stmt->execute([$userId]);

        session_destroy();
        header("Location: home.php");
        exit;
    }

    // Mise à jour des infos utilisateur
    if (!empty($_POST['email'])) {
        $stmt = $pdo->prepare("UPDATE Users SET email = ? WHERE id = ?");
        $stmt->execute([$_POST['email'], $userId]);
        echo "Email mis à jour.<br>";
    }
    if (!empty($_POST['password'])) {
        $hashed = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE Users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $userId]);
        echo "Mot de passe mis à jour.<br>";
    }
    if (!empty($_POST['add_balance'])) {
        $amount = floatval($_POST['add_balance']);
        $stmt = $pdo->prepare("UPDATE Users SET solde = solde + ? WHERE id = ?");
        $stmt->execute([$amount, $userId]);
        echo "Solde mis à jour.<br>";
    }

    // Mise à jour de la photo de profil
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['photo_profil']['tmp_name'];
        $fileName = basename($_FILES['photo_profil']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExt, $allowed)) {
            // Créer le dossier uploads si non existant
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }

            $newFileName = 'uploads/photo_' . $userId . '_' . time() . '.' . $fileExt;
            if (move_uploaded_file($fileTmp, $newFileName)) {
                // Mettre à jour la BDD
                $stmt = $pdo->prepare("UPDATE Users SET photo_profil = ? WHERE id = ?");
                $stmt->execute([$newFileName, $userId]);
                echo "Photo de profil mise à jour.<br>";
            } else {
                echo "Erreur lors de l'upload de l'image.";
            }
        } else {
            echo "Type de fichier non autorisé.";
        }
    }

    // Rechargement pour afficher la nouvelle photo
    header("Location: compte.php");
    exit;
}
?>

<?php include 'header.php'; ?>

<h1>Compte de <?= htmlspecialchars($user['username']) ?></h1>
<p>Email : <?= htmlspecialchars($user['email']) ?></p>
<p>Solde : <?= number_format($user['solde'], 2) ?> €</p>

<?php if ($isOwnAccount): ?>
    <!-- Formulaire de mise à jour de la photo -->
    <form method="POST" enctype="multipart/form-data" id="formPhoto">
        <input type="file" name="photo_profil" id="photoInput" style="display:none;" accept="image/*" onchange="document.getElementById('formPhoto').submit();">
        <?php if (!empty($user['photo_profil'])): ?>
            <img src="<?= htmlspecialchars($user['photo_profil']) ?>" width="100" alt="Photo de profil" style="cursor: pointer;" onclick="document.getElementById('photoInput').click();">
        <?php else: ?>
            <img src="default.jpg" width="100" alt="Photo de profil" style="cursor: pointer;" onclick="document.getElementById('photoInput').click();">
        <?php endif; ?>
    </form>
<?php else: ?>
    <?php if (!empty($user['photo_profil'])): ?>
        <img src="<?= htmlspecialchars($user['photo_profil']) ?>" width="100" alt="Photo de profil">
    <?php else: ?>
        <img src="default.jpg" width="100" alt="Photo de profil">
    <?php endif; ?>
<?php endif; ?>

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
        <li>
            <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="<?= htmlspecialchars($article['nom']) ?>" width="50" style="vertical-align:middle; margin-right:8px;">
            <?= htmlspecialchars($article['nom']) ?> (<?= number_format($article['prix'], 2) ?> €)
        </li>
    <?php endforeach; ?>
    </ul>

    <h2>Vos factures</h2>
    <ul>
    <?php foreach ($invoices as $invoice): ?>
        <li>
            <a href="facture.php?id=<?= $invoice['id'] ?>">
                Facture #<?= $invoice['id'] ?> - <?= number_format($invoice['montant'], 2) ?> € - <?= htmlspecialchars($invoice['date_transaction']) ?>
            </a>
        </li>
    <?php endforeach; ?>
    </ul>

    <h2>Modifier vos informations</h2>
    <form method="POST">
        <label>Nouvel email : <input type="email" name="email"></label><br>
        <label>Nouveau mot de passe : <input type="password" name="password"></label><br>
        <label>Ajouter au solde (€) : <input type="number" step="0.01" name="add_balance"></label><br>
        <button type="submit">Mettre à jour</button>
    </form>

    <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.');" style="margin-top:10px;">
        <input type="hidden" name="delete_account" value="1">
        <button type="submit" style="color: red;">Supprimer le compte</button>
    </form>
<?php endif; ?>
