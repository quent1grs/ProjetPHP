<?php
session_start();
require 'db.php';

// Vérifier que l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); // ou page d'erreur
    exit;
}

// Récupérer l'ID de l'utilisateur à modifier
$userId = $_GET['id'] ?? null;
if (!$userId) {
    echo "ID utilisateur manquant.";
    exit;
}

// Récupérer l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM Users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    echo "Utilisateur introuvable.";
    exit;
}

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'client'; // Par défaut client
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email)) {
        $error = "Le nom d'utilisateur et l'email sont obligatoires.";
    } else {
        if (!empty($password)) {
            // Hasher le nouveau mot de passe
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("UPDATE Users SET username = ?, email = ?, role = ?, password = ? WHERE id = ?");
            $stmt->execute([$username, $email, $role, $hashedPassword, $userId]);
        } else {
            // Sans changer le mot de passe
            $stmt = $pdo->prepare("UPDATE Users SET username = ?, email = ?, role = ? WHERE id = ?");
            $stmt->execute([$username, $email, $role, $userId]);
        }

        header('Location: admin.php');
        exit;
    }
}
?>

<h1>Modifier utilisateur #<?= htmlspecialchars($user['id']) ?></h1>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">
    <label>Nom d'utilisateur : <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required></label><br><br>
    <label>Email : <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></label><br><br>
    <label>Rôle : 
        <select name="role" required>
            <option value="client" <?= $user['role'] === 'client' ? 'selected' : '' ?>>Client</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
    </label><br><br>
    <label>Mot de passe (laisser vide pour ne pas changer) : <input type="password" name="password"></label><br><br>
    <button type="submit">Mettre à jour</button>
</form>

<a href="admin.php">← Retour à l'administration</a>
