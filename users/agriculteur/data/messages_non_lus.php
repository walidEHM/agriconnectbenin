<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['agriculteur_id'])) {
    echo json_encode(['unread' => 0]);
    exit();
}
require_once __DIR__ . '/../../../data/dbconn.php';
$agriculteur_id = $_SESSION['agriculteur_id'];
$stmt = $conn->prepare("SELECT COUNT(*) as unread FROM messages WHERE destinataire_id = ? AND destinataire_type = 'agriculteur' AND lu = 0");
$stmt->execute([$agriculteur_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode(['unread' => (int)($row['unread'] ?? 0)]); 