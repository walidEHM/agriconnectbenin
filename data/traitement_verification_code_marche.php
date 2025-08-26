<?php
require_once __DIR__ . '/dbconn.php';

// ➤ 1. Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('location: identification_marche.php#showLoginForm');
    exit();
}

// ➤ 2. Vérifier la présence de l'email
if (!isset($_POST['email'])) {
    header('location: identification_marche.php#showLoginForm');
    exit();
}

$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    header('location: identification_marche.php#showLoginForm');
    exit();
}

// ➤ 3. Récupérer les chiffres du code
$code = '';
for ($i = 1; $i <= 6; $i++) {
    $digit = $_POST['code' . $i] ?? '';
    if (!ctype_digit($digit) || strlen($digit) !== 1) {
        header('location: ../verifier_code_de_mot_de_passe_oublie_marche.php?email=' . urlencode($email) . '&error=code_invalide');
        exit();
    }
    $code .= $digit;
}

// ➤ 4. Vérifier l'existence de l'utilisateur et du code
$sql = 'SELECT id, token_verification, expiration_token FROM marches 
        WHERE email = ? AND compte_active = 1';
$stmt = $conn->prepare($sql);
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('location: identification_marche.php#showLoginForm');
    exit();
}

// ➤ 5. Vérifier expiration du code
$now = new DateTime();
$expiration = new DateTime($user['expiration_token']);

if ($now > $expiration) {
    header('location: ../verifier_code_de_mot_de_passe_oublie_marche.php?email=' . urlencode($email) . '&error=code_expire');
    exit();
}

// ➤ 6. Comparer les codes
if ($code !== $user['token_verification']) {
    header('location: ../verifier_code_de_mot_de_passe_oublie_marche.php?email=' . urlencode($email) . '&error=code_incorrect');
    exit();
}

// ➤ 7. Créer un token pour la réinitialisation du mot de passe
$reset_token = bin2hex(random_bytes(32));
$reset_expiration = (new DateTime())->add(new DateInterval('PT1H'))->format('Y-m-d H:i:s');

// ➤ 8. Mise à jour dans la base
$sql = 'UPDATE marches 
        SET token_reinitialisation = ?, expiration_reinitialisation = ?, 
            token_verification = NULL, expiration_token = NULL 
        WHERE email = ?';
$stmt = $conn->prepare($sql);
$stmt->execute([$reset_token, $reset_expiration, $email]);

// ➤ 9. Redirection vers la page pour définir un nouveau mot de passe
header('location: ../nouveau_password_marche.php?email=' . urlencode($email) . '&token=' . $reset_token);
exit();
?>
