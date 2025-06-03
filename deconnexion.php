 php
<?php
session_start();
session_destroy();

// Redirection vers la page d'accueil après déconnexion
header('Location: home.php');
exit();