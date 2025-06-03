<?php
session_start();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'ecommerce');

    if ($conn->connect_error) {
        die('Connexion échouée : ' . $conn->connect_error);
    }

    // Création des variables
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Vérifier si l'utilisateur existe déjà
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Ce nom d'utilisateur ou cet email existe déjà.";
    } else {
        // Insérer le nouvel utilisateur
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'client')");
        $stmt->bind_param("sss", $username, $password, $email);

        // Lance la requête à la bdd
        if ($stmt->execute()) {
            $success = "Compte créé <a href='connexion.php'>Connecte-toi</a>";
        } else {
            $error = "Erreur lors de l'inscription.";
        }

        $stmt->close();
    }

    $check->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Inscription</title>
</head>
<body>

    <h1>Enregistre-toi</h1>

    <?php if (!empty($error)): ?>
        <div style="color:red;"><?= $error ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div style="color:green;"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <div>Nom d'utilisateur :</div>
        <input type="text" name="username" required>

        <div>Email :</div>
        <input type="email" name="email" required>

        <div>Mot de passe :</div>
        <input type="password" name="password" required>

        <br><br>
        <button type="submit" href="home.php">S'inscrire</button>
    </form>

    <div>Déjà inscrit ?</div>
    <a href="connexion.php">Connecte-toi</a>

    <style>
        body {
            background: red;
            color: white;
            font-family: Arial, sans-serif;
        }

        input, button {
            margin: 5px 0;
            padding: 5px;
        }

        form {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 5px;
            width: 300px;
        }
    </style>
</body>
</html>
