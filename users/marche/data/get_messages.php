<?php
session_start();
require_once __DIR__ . '/../../data/dbconn.php';

// Vérification de la session
if (!isset($_SESSION['marche_id']) && !isset($_SESSION['agriculteur_id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

$current_user = [
    'id' => $_SESSION['marche_id'] ?? $_SESSION['agriculteur_id'],
    'type' => isset($_SESSION['marche_id']) ? 'marche' : 'agriculteur'
];

$contact_id = $_GET['contact_id'] ?? null;
$contact_type = $_GET['contact_type'] ?? null;
$last_message_id = $_GET['last_message_id'] ?? 0;

if (!$contact_id || !$contact_type) {
    header("HTTP/1.1 400 Bad Request");
    exit();
}

try {
    $stmt = $conn->prepare("SELECT m.* FROM messages m
        JOIN conversations c ON m.conversation_id = c.id
        WHERE ((c.participant1_id = ? AND c.participant1_type = ? AND c.participant2_id = ? AND c.participant2_type = ?) OR
              (c.participant1_id = ? AND c.participant1_type = ? AND c.participant2_id = ? AND c.participant2_type = ?))
        AND m.id > ?
        ORDER BY m.date_envoi ASC");
    $stmt->execute([
        $current_user['id'], $current_user['type'], $contact_id, $contact_type,
        $contact_id, $contact_type, $current_user['id'], $current_user['type'],
        $last_message_id
    ]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Marquer les messages comme lus
    if (!empty($messages)) {
        $stmt = $conn->prepare("UPDATE messages SET lu = 1 
            WHERE destinataire_id = ? AND destinataire_type = ? 
            AND expediteur_id = ? AND expediteur_type = ?
            AND id <= ?");
        $stmt->execute([
            $current_user['id'], $current_user['type'],
            $contact_id, $contact_type,
            end($messages)['id']
        ]);
    }
    
    header('Content-Type: application/json');
    echo json_encode(['messages' => $messages]);
    
} catch (PDOException $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['error' => $e->getMessage()]);
}