<?php
session_start();

header('Content-Type: application/json');

// Vérification de session
if (!isset($_SESSION['agriculteur_id']) || !isset($_SESSION['agriculteur_connecte']) || $_SESSION['agriculteur_connecte'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

require_once __DIR__ . '/../../../data/dbconn.php';

try {
    // Récupération des données
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Données JSON invalides');
    }

    // Validation des champs
    $requiredFields = ['post_id', 'action', 'user_type', 'user_id'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            throw new Exception('Champ manquant: ' . $field);
        }
    }

    $postId = (int)$data['post_id'];
    $userId = (int)$data['user_id'];
    $action = $data['action'] === 'like' ? 'like' : 'unlike';
    $userType = $data['user_type'];

    // Validation des IDs
    if ($postId <= 0 || $userId <= 0) {
        throw new Exception('ID invalide');
    }

    // Vérification de la publication
    $stmt = $conn->prepare("SELECT id FROM publications WHERE id = ?");
    $stmt->execute([$postId]);
    if ($stmt->rowCount() === 0) {
        throw new Exception('Publication non trouvée');
    }

    // Traitement
    $conn->beginTransaction();

    if ($action === 'like') {
        // Vérifie si like existe déjà
        $stmt = $conn->prepare("SELECT id FROM likes WHERE publication_id = ? AND utilisateur_id = ? AND utilisateur_type = ?");
        $stmt->execute([$postId, $userId, $userType]);
        
        if ($stmt->rowCount() === 0) {
            $stmt = $conn->prepare("INSERT INTO likes (publication_id, utilisateur_id, utilisateur_type, date_like) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$postId, $userId, $userType]);
        }
    } else {
        $stmt = $conn->prepare("DELETE FROM likes WHERE publication_id = ? AND utilisateur_id = ? AND utilisateur_type = ?");
        $stmt->execute([$postId, $userId, $userType]);
    }

    // Compter les likes actuels
    $stmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE publication_id = ?");
    $stmt->execute([$postId]);
    $likesCount = (int)$stmt->fetchColumn();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'action' => $action,
        'likesCount' => $likesCount
    ]);

} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    error_log('Database Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'error' => $e->getMessage() // À supprimer en production
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}?>