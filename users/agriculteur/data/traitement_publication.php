<?php
session_start();
require_once __DIR__ . '/../../../data/dbconn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Méthode non autorisée']));
}

if (!isset($_SESSION['agriculteur_id'])) {
    die(json_encode(['success' => false, 'message' => 'Non authentifié']));
}

$content = trim($_POST['content'] ?? '');
$agriculteur_id = $_SESSION['agriculteur_id'];
$media_paths = [];

// Vérification des fichiers
if (isset($_FILES['media']) && is_array($_FILES['media']['name'])) {
    $fileCount = count($_FILES['media']['name']);
    
    // Limite à 3 fichiers
    if ($fileCount > 3) {
        die(json_encode(['success' => false, 'message' => 'Maximum 3 photos autorisées']));
    }
    
    // Dossier de destination
    $uploadDir = __DIR__ . '/../assets/images/image_publier/';
    
    // Créer le dossier s'il n'existe pas
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    for ($i = 0; $i < $fileCount; $i++) {
        if ($_FILES['media']['error'][$i] !== UPLOAD_ERR_OK) continue;
        
        // Validation du type de fichier
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['media']['tmp_name'][$i]);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            continue;
        }
        
        // Validation de la taille (optionnelle)
        if ($_FILES['media']['size'][$i] > 5 * 1024 * 1024) { // 5MB max par fichier
            continue;
        }
        
        // Génération d'un nom de fichier unique
        $extension = pathinfo($_FILES['media']['name'][$i], PATHINFO_EXTENSION);
        $filename = 'pub_' . $agriculteur_id . '_' . uniqid() . '_' . $i . '.' . $extension;
        $destination = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['media']['tmp_name'][$i], $destination)) {
            $media_paths[] = $filename;
        }
    }
}

try {
    // Enregistrement en base de données
    $conn->beginTransaction();
    
    // Insertion de la publication
    $stmt = $conn->prepare("INSERT INTO publications (agriculteur_id, contenu, media_chemin) VALUES (?, ?, ?)");
    $media_chemin = !empty($media_paths) ? implode(',', $media_paths) : null;
    $stmt->execute([$agriculteur_id, $content, $media_chemin]);
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Publication créée avec succès']);
} catch (Exception $e) {
    $conn->rollBack();
    
    // Supprimer les fichiers uploadés en cas d'erreur
    foreach ($media_paths as $file) {
        if (file_exists($uploadDir . $file)) {
            unlink($uploadDir . $file);
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}