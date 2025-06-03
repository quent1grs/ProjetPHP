<?php
?>

<header style="background:#f0f0f0; padding:10px; display:flex; justify-content:space-between; align-items:center;">
    <nav>
        <a href="home.php" style="margin-right:15px;">🏠 Home</a>
        <a href="cart.php" style="margin-right:15px;">🛒 Panier</a>
    </nav>

    <nav>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="compte.php" style="margin-right:15px;">Voir profil</a>
            <a href="deconnexion.php">Déconnexion</a>
        <?php else: ?>
            <a href="connexion.php" style="margin-right:15px;">Connexion</a>
            <a href="register.php">Enregistrement</a>
        <?php endif; ?>
    </nav>
</header>
<hr>