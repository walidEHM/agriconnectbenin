<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['marche_id'])) {
    echo json_encode(['unread' => 0]);
    exit();
}
require_once __DIR__ . '/../../../data/dbconn.php';
$marche_id = $_SESSION['marche_id'];
$stmt = $conn->prepare("SELECT COUNT(*) as unread FROM messages WHERE destinataire_id = ? AND destinataire_type = 'marche' AND lu = 0");
$stmt->execute([$marche_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode(['unread' => (int)($row['unread'] ?? 0)]); 