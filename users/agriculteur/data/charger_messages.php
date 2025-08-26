<?php
session_start();
if (!isset($_SESSION['agriculteur_id']) || !isset($_GET['conversation_id'])) {
    http_response_code(403);
    exit('Accès refusé');
}
require_once __DIR__ . '/../../../data/dbconn.php';
$agriculteur_id = $_SESSION['agriculteur_id'];
$conversation_id = (int)$_GET['conversation_id'];

// Marquer comme lus les messages reçus
$conn->prepare("UPDATE messages SET lu = 1 WHERE conversation_id = ? AND destinataire_id = ? AND destinataire_type = 'agriculteur'")
    ->execute([$conversation_id, $agriculteur_id]);

// Récupérer les messages
$stmt = $conn->prepare("SELECT * FROM messages WHERE conversation_id = ? ORDER BY date_envoi ASC");
$stmt->execute([$conversation_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($messages as $msg) {
    $isMe = $msg['expediteur_type'] === 'agriculteur' && $msg['expediteur_id'] == $agriculteur_id;
    echo '<div class="message-row'.($isMe ? ' me' : '').'">';
    echo '<div class="message-bubble">'.nl2br(htmlspecialchars($msg['contenu'])).'</div>';
    echo '<div class="message-meta">'.date('d/m/Y H:i', strtotime($msg['date_envoi'])).'</div>';
    echo '</div>';
} 