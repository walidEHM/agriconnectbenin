<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['agriculteur_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}
require_once __DIR__ . '/../../../data/dbconn.php';
$agriculteur_id = $_SESSION['agriculteur_id'];
$data = json_decode(file_get_contents('php://input'), true);
$conversation_id = isset($data['conversation_id']) ? (int)$data['conversation_id'] : 0;
$message = trim($data['message'] ?? '');
if (!$conversation_id || $message === '') {
    echo json_encode(['success' => false, 'message' => 'Message vide ou conversation invalide']);
    exit();
}
// Trouver le destinataire
$stmt = $conn->prepare("SELECT participant1_id, participant1_type, participant2_id, participant2_type FROM conversations WHERE id = ?");
$stmt->execute([$conversation_id]);
$conv = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$conv) {
    echo json_encode(['success' => false, 'message' => 'Conversation introuvable']);
    exit();
}
if ($conv['participant1_id'] == $agriculteur_id && $conv['participant1_type'] == 'agriculteur') {
    $dest_id = $conv['participant2_id'];
    $dest_type = $conv['participant2_type'];
} else {
    $dest_id = $conv['participant1_id'];
    $dest_type = $conv['participant1_type'];
}
// Insérer le message
$stmt = $conn->prepare("INSERT INTO messages (conversation_id, expediteur_id, expediteur_type, destinataire_id, destinataire_type, contenu, date_envoi, lu) VALUES (?, ?, 'agriculteur', ?, ?, ?, NOW(), 0)");
$ok = $stmt->execute([$conversation_id, $agriculteur_id, $dest_id, $dest_type, $message]);
echo json_encode(['success' => $ok]); 