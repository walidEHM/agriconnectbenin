<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../data/dbconn.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $userId = $data['user_id'] ?? null;
    $userType = $data['user_type'] ?? '';

    $query = "SELECT 
        p.*, 
        a.nom_complet, 
        a.photo_profil,
        a.communes,
        (SELECT COUNT(*) FROM likes WHERE publication_id = p.id) AS nombre_likes,
        (SELECT COUNT(*) FROM likes WHERE publication_id = p.id AND utilisateur_id = :userId AND utilisateur_type = :userType) AS deja_like
    FROM publications p
    JOIN agriculteurs a ON p.agriculteur_id = a.id
    WHERE a.compte_verifie = 1
    ORDER BY p.date_publication DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute([':userId' => $userId, ':userType' => $userType]);
    $publications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formater les dates
    foreach ($publications as &$pub) {
        $pub['date_publication'] = date('d/m/Y H:i', strtotime($pub['date_publication']));
    }

    echo json_encode([
        'success' => true,
        'publications' => $publications
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
}
?>