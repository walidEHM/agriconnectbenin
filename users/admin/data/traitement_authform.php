<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
session_unset();
session_destroy();
session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/../../../data/dbconn.php';

$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = "Méthode non autorisée";
    echo json_encode($response);
    exit();
}

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!$data || empty($data['username']) || empty($data['password'])) {
    $response['message'] = "Tous les champs sont obligatoires";
    echo json_encode($response);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = :username");
    $stmt->bindParam(':username', $data['username'], PDO::PARAM_STR);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($data['password'], $admin['password_hash'])) {
        $_SESSION['token_admin_verification'] = sprintf("%06d", random_int(0, 999999));
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_verified'] = true;
        $_SESSION['admin'] = true;
        $_SESSION['admin_email'];

        session_regenerate_id(true);

        $response['success'] = true;
        $response['message'] = "Connexion réussie";
        $response['redirect'] = '../index.php';
    } else {
        $response['message'] = "Identifiants incorrects";
    }
} catch (PDOException $e) {
    error_log("Erreur PDO : " . $e->getMessage());
    $response['message'] = "Erreur système. Veuillez réessayer.";
}

echo json_encode($response);
exit();
