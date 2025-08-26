<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['agriculteur_id'], $_SESSION['agriculteur_connecte']) || $_SESSION['agriculteur_connecte'] !== true) {
    echo json_encode(['success' => false, 'message' => "Non autorisé."]);
    exit();
}
require_once __DIR__ . '/../../../data/dbconn.php';
$agriculteur_id = $_SESSION['agriculteur_id'];
$data = json_decode(file_get_contents('php://input'), true);
$old = $data['old_password'] ?? '';
$new = $data['new_password'] ?? '';
$confirm = $data['confirm_password'] ?? '';
$errors = [];
if (empty($old) || empty($new) || empty($confirm)) {
    $errors[] = "Tous les champs sont obligatoires.";
} elseif (strlen($new) < 8) {
    $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
} elseif ($new !== $confirm) {
    $errors[] = "Les mots de passe ne correspondent pas.";
}
if (!$errors) {
    $stmt = $conn->prepare("SELECT mot_de_passe FROM agriculteurs WHERE id = ?");
    $stmt->execute([$agriculteur_id]);
    $hash = $stmt->fetchColumn();
    if (!$hash || !password_verify($old, $hash)) {
        $errors[] = "Ancien mot de passe incorrect.";
    } elseif (password_verify($new, $hash)) {
        $errors[] = "Le nouveau mot de passe doit être différent de l'ancien.";
    }
}
if ($errors) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit();
}
try {
    $new_hash = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE agriculteurs SET mot_de_passe = ? WHERE id = ?");
    $stmt->execute([$new_hash, $agriculteur_id]);
    echo json_encode(['success' => true, 'message' => "Mot de passe modifié avec succès."]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => "Erreur technique."]);
} 