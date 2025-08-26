<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération du code saisi dans les 6 champs code1, code2, ..., code6
    $code = '';
    for ($i = 1; $i <= 6; $i++) {
        $code .= $_POST["code$i"] ?? '';
    }

    // Vérifier que le code existe en session
    if (!isset($_SESSION['token_admin_verification'])) {
        echo 'Code de vérification non trouvé en session. Veuillez refaire la connexion.';
        exit();
    }

    $code_true = $_SESSION['token_admin_verification'];

    // Comparaison des codes
    if ($code === $code_true) {
        $_SESSION['admin_verified'] = true;
        $_SESSION['admin'] = true;
        unset($_SESSION['token_admin_verification']);
        $_SESSION['admin_email'];
        unset($_SESSION['authemail']);

        // Redirection vers la page d'accueil admin (à adapter)
        header('Location: ../index.php');
        exit();
    } else {
        // Code incorrect - message d'erreur ou retour formulaire
        echo 'Code incorrect, veuillez réessayer.';
        // Optionnel : proposer un lien pour revenir au formulaire ou renvoyer la page
    }
} else {
    echo 'Accès non autorisé.';
    exit();
}
?>
