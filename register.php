<?php
session_start();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'ecommerce');

    if ($conn->connect_error) {
        die('Connexion échouée : ' . $conn->connect_error);
    }

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password_raw = $_POST['password'];

    $profilePicturePath = 'images/default.png'; // chemin par défaut
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir);

        $filename = basename($_FILES['profile_picture']['name']);
        $targetPath = $uploadDir . uniqid() . '_' . $filename;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
            $profilePicturePath = $targetPath;
        } else {
            $error = "Erreur lors du téléchargement de l'image.";
        }
    }

    if (strlen($username) < 3 || strlen($username) > 20) {
        $error = "Le nom d'utilisateur doit contenir entre 3 et 20 caractères.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email est invalide.";
    } elseif (strlen($password_raw) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Ce nom d'utilisateur ou cet email existe déjà.";
        } else {
            $password = password_hash($password_raw, PASSWORD_BCRYPT);

            $stmt = $conn->prepare("INSERT INTO users (username, password, email, role, photo_profil) VALUES (?, ?, ?, 'client', ?)");
            $stmt->bind_param("ssss", $username, $password, $email, $profilePicturePath);

            if ($stmt->execute()) {
                $success = "Compte créé avec succès ! <a href='connexion.php'>Connecte-toi ici</a>";
            } else {
                $error = "Erreur lors de l'inscription.";
            }

            $stmt->close();
        }

        $check->close();
    }

    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <style>
        body {
            background: #e63946;
            color: white;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            width: 320px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        input, button {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            border-radius: 5px;
            border: none;
        }

        button {
            background-color: #1d3557;
            color: white;
            cursor: pointer;
        }

        .message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
        }

        .error {
            background-color: rgba(255, 0, 0, 0.2);
        }

        .success {
            background-color: rgba(0, 255, 0, 0.2);
        }

        a {
            color: #f1faee;
        }

        .bottom-link {
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>

    <form method="POST" enctype="multipart/form-data">
        <h2>Créer un compte</h2>

        <?php if (!empty($error)): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="message success"><?= $success ?></div>
        <?php endif; ?>
        <label>Photo de profil :</label>
        <input type="file" name="profile_picture" accept="image/*" required>


        <label>Nom d'utilisateur :</label>
        <input type="text" name="username" required>

        <label>Email :</label>
        <input type="email" name="email" required>

        <label>Mot de passe :</label>
        <input type="password" name="password" required>

        <button type="submit" href="home.php">S'inscrire</button>

        <div class="bottom-link">
            Déjà inscrit ? <a href="connexion.php">Connecte-toi</a>
        </div>
    </form>

</body>
</html>
