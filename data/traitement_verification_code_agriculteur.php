<?php
require_once __DIR__.'/dbconn.php';

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('location: identification_agriculteur.php#showLoginForm');
    exit();
}

// Vérifier l'email
if (!isset($_POST['email'])) {
    header('location: identification_agriculteur.php#showLoginForm');
    exit();
}

$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    header('location: identification_agriculteur.php#showLoginForm');
    exit();
}

// Récupérer les chiffres du code
$code = '';
for ($i = 1; $i <= 6; $i++) {
    $digit = $_POST['code'.$i] ?? '';
    if (!ctype_digit($digit) || strlen($digit) !== 1) {
        // Code invalide
        header('location: ../verifier_code_de_mot_de_passe_oublie_agriculteur.php?email='.urlencode($email).'&error=code_invalide');
        exit();
    }
    $code .= $digit;
}

// Vérifier le code dans la base de données
$sql = 'SELECT id, token_verification, expiration_token FROM agriculteurs 
        WHERE email = ? AND compte_active = 1';
$stmt = $conn->prepare($sql);
$stmt->execute([$email]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    // Aucun utilisateur trouvé
    header('location: identification_agriculteur.php#showLoginForm');
    exit();
}

// Vérifier la date d'expiration
$now = new DateTime();
$expiration = new DateTime($result['expiration_token']);

if ($now > $expiration) {
    // Le token a expiré
    header('location: ../verifier_code_de_mot_de_passe_oublie_agriculteur.php?email='.urlencode($email).'&error=code_expire');
    exit();
}

// Vérifier si le code correspond
if ($code !== $result['token_verification']) {
    // Code incorrect
    header('location: ../verifier_code_de_mot_de_passe_oublie_agriculteur.php?email='.urlencode($email).'&error=code_incorrect');
    exit();
}

// Code correct - Créer un token pour la réinitialisation du mot de passe
$reset_token = bin2hex(random_bytes(32));
$reset_expiration = (new DateTime())->add(new DateInterval('PT1H'))->format('Y-m-d H:i:s');

// Stocker le token dans la base de données
$sql = 'UPDATE agriculteurs 
        SET token_reinitialisation = ?, expiration_reinitialisation = ?, token_verification = NULL, expiration_token = NULL
        WHERE email = ?';
$stmt = $conn->prepare($sql);
$stmt->execute([$reset_token, $reset_expiration, $email]);

// Rediriger vers la page de réinitialisation du mot de passe
header('location: ../nouveau_password_agriculteur.php?email='.urlencode($email).'&token='.$reset_token);
exit();
?>