<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['marche_id'], $_SESSION['marche_connecte']) || $_SESSION['marche_connecte'] !== true) {
    echo json_encode(['success' => false, 'message' => "Non autorisé."]);
    exit();
}
require_once __DIR__ . '/../../../data/dbconn.php';
$marche_id = $_SESSION['marche_id'];
$data = json_decode(file_get_contents('php://input'), true);
$nom = trim($data['nom'] ?? '');
$email = trim($data['email'] ?? '');
$errors = [];
if (empty($nom) || strlen($nom) > 100) {
    $errors[] = "Le nom doit contenir entre 1 et 100 caractères.";
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "L'email n'est pas valide.";
}
if ($errors) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit();
}
try {
    $stmt = $conn->prepare("UPDATE marches SET nom = ?, email = ? WHERE id = ?");
    $stmt->execute([$nom, $email, $marche_id]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => "Profil mis à jour avec succès."]);
    } else {
        echo json_encode(['success' => true, 'message' => "Aucune modification détectée."]);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => "Erreur technique."]);
} 