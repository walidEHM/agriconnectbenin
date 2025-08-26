<?php
session_start();
header('Content-Type: application/json');

// Désactiver l'affichage des erreurs
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../../data/dbconn.php';

$response = ['success' => false, 'message' => 'Erreur inconnue'];

try {
    // Vérification de session
    if (!isset($_SESSION['marche_id'], $_SESSION['marche_connecte']) || $_SESSION['marche_connecte'] !== true) {
        throw new Exception('Accès non autorisé', 401);
    }

    // Récupération des données
    $input = file_get_contents('php://input');
    if (empty($input)) {
        throw new Exception('Aucune donnée reçue', 400);
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Données JSON invalides', 400);
    }

    // Validation des paramètres obligatoires
    if (!isset($data['user_id'], $data['user_type'])) {
        throw new Exception('Paramètres manquants', 400);
    }

    // Construction de la requête de base
    $query = "SELECT p.*, a.nom_complet, a.photo_profil, a.communes,
              (SELECT COUNT(*) FROM likes WHERE publication_id = p.id) AS nombre_likes,
              (SELECT COUNT(*) FROM likes WHERE publication_id = p.id AND utilisateur_id = :user_id AND utilisateur_type = :user_type) AS deja_like
              FROM publications p
              JOIN agriculteurs a ON p.agriculteur_id = a.id
              WHERE a.compte_verifie = 1";

    $params = [
        ':user_id' => $data['user_id'],
        ':user_type' => $data['user_type']
    ];

    // Ajout des filtres conditionnels
    if (!empty($data['commune'])) {
        $query .= " AND FIND_IN_SET(:commune, a.communes) > 0";
        $params[':commune'] = $data['commune'];
    }

    if (!empty($data['keyword'])) {
        $query .= " AND (p.contenu LIKE :keyword OR a.nom_complet LIKE :keyword)";
        $params[':keyword'] = '%' . $data['keyword'] . '%';
    }

    $query .= " ORDER BY p.date_publication DESC";

    // Préparation et exécution de la requête
    $stmt = $conn->prepare($query);
    
    // Liaison des paramètres
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    if (!$stmt->execute()) {
        throw new Exception('Erreur lors de l\'exécution de la requête', 500);
    }

    $publications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatage de la réponse
    $response = [
        'success' => true,
        'publications' => array_map(function($pub) {
            return [
                'id' => $pub['id'],
                'agriculteur_id' => $pub['agriculteur_id'],
                'nom_complet' => $pub['nom_complet'],
                'photo_profil' => $pub['photo_profil'],
                'communes' => $pub['communes'],
                'contenu' => $pub['contenu'],
                'media_chemin' => $pub['media_chemin'],
                'date_publication' => date('d/m/Y H:i', strtotime($pub['date_publication'])),
                'nombre_likes' => (int)$pub['nombre_likes'],
                'deja_like' => (bool)$pub['deja_like']
            ];
        }, $publications)
    ];

} catch (PDOException $e) {
    $response = [
        'success' => false,
        'message' => 'Erreur de base de données',
        'debug' => $e->getMessage() // À supprimer en production
    ];
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ];
}

// Envoyer la réponse JSON
echo json_encode($response);
exit();
?>