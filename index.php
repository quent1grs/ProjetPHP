<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Exemple PHP</title>
</head>
<body>
    <h1>Bonjour vous</h1>
    <p>Nous sommes le <?php echo date("d/m/Y"); ?></p>
    <a href="connexion.php">Connexion</a>
    <a href="register.php">Enregistrement</a>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="compte.php">Voir profil</a>
    <?php endif; ?>
</body>
</html>
