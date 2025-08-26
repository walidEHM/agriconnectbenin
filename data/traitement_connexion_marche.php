<?php
session_start();
session_unset();
session_destroy();
session_start();

// Configuration de l'affichage des erreurs (à désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure le fichier de connexion à la base de données
require_once __DIR__ . '/dbconn.php';

// Fonction pour rediriger avec une alerte JavaScript
function redirectWithAlert($message, $location) {
    echo "<script>
        alert('" . addslashes($message) . "');
        window.location.href = '" . $location . "';
    </script>";
    exit();
}

// Vérifier que la requête est bien de type POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyer les données d'entrée
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    // Valider les champs obligatoires
    if (empty($email) || empty($mot_de_passe)) {
        redirectWithAlert("Veuillez remplir tous les champs.", "../identification_marche.php#showLoginForm");
    }

    // Valider le format de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWithAlert("L'adresse email n'est pas valide.", "../identification_marche.php#showLoginForm");
    }

    try {
        // Préparer et exécuter la requête pour trouver le marché
        $sql = "SELECT *
                FROM marches 
                WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $marche = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifier si le marché existe et le mot de passe est correct
        if ($marche) {
            if (password_verify($mot_de_passe, $marche['mot_de_passe'])) {
                // Vérifier si le compte est activé
                if ($marche['compte_active'] != 1) {
                    redirectWithAlert("Votre compte n'est pas encore activé. Veuillez vérifier votre email ou inscrivez-vous à nouveau.", "../identification_marche.php#showLoginForm");
                }

                // Créer la session du marché
                $_SESSION['marche_id'] = $marche['id'];
                $_SESSION['marche_nom'] = $marche['nom'];
                $_SESSION['marche_email'] = $marche['email'];
                $_SESSION['marche_connecte'] = true;

                // Régénérer l'ID de session pour éviter les attaques par fixation
                session_regenerate_id(true);

                // Rediriger vers le tableau de bord avec JavaScript
                echo "<script>window.location.href = '../users/marche/index.php';</script>";
                exit();
            } else {
                // Mot de passe incorrect
                redirectWithAlert("Email ou mot de passe incorrect.", "../identification_marche.php#showLoginForm");
            }
        } else {
            // Aucun marché trouvé avec cet email
            redirectWithAlert("Email ou mot de passe incorrect.", "../identification_marche.php#showLoginForm");
        }
    } catch (PDOException $e) {
        // Journaliser l'erreur et afficher un message générique
        error_log("Erreur de connexion: " . $e->getMessage());
        redirectWithAlert("Une erreur est survenue lors de la connexion. Veuillez réessayer plus tard.", "../identification_marche.php#showLoginForm");
    }
} else {
    // Rediriger si l'accès n'est pas via POST
    redirectWithAlert("Accès non autorisé.", "../identification_marche.php#showLoginForm");
}