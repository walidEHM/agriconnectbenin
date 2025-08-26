<?php
header('Content-Type: application/json');
session_start();

// Configuration
$uploadDir = __DIR__ . '/../uploads/';
$maxFileSize = 3 * 1024 * 1024; // 3 Mo
$allowedTypes = [
    'identity' => ['image/jpeg', 'image/png', 'application/pdf'],
    'certificate' => ['image/jpeg', 'image/png', 'application/pdf']
];

// Initialisation réponse
$response = [
    'success' => false,
    'message' => 'Erreur de traitement',
    'errors' => []
];

try {
    // Vérification session
    if (empty($_SESSION['agriculteur_id']) || empty($_SESSION['agriculteur_connecte']) || $_SESSION['agriculteur_connecte'] !== true) {
        throw new Exception('Authentification requise', 401);
    }

    $agriculteurId = (int)$_SESSION['agriculteur_id'];
    if ($agriculteurId <= 0) {
        throw new Exception('ID agriculteur invalide', 400);
    }

    // Validation données
    $errors = [];
    
    // Téléphone
    $telephone = preg_replace('/[^0-9]/', '', $_POST['telephone'] ?? '');
    if (!preg_match('/^[0-9]{10}$/', $telephone)) {
        $errors['phone'] = 'Numéro invalide (10 chiffres requis)';
    }

    // Communes
    $communes = json_decode($_POST['communes'] ?? '[]', true) ?? [];
    if (empty($communes)) {
        $errors['commune'] = 'Sélectionnez au moins une commune';
    }

    // Traitement fichiers
    $uploadedFiles = [];
    foreach ($allowedTypes as $field => $mimeTypes) {
        if (empty($_FILES[$field]['name'])) {
            $errors[$field] = 'Fichier requis';
            continue;
        }

        $file = $_FILES[$field];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[$field] = 'Erreur lors du téléversement';
            continue;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        
        if (!in_array($mime, $mimeTypes)) {
            $errors[$field] = 'Type de fichier non autorisé';
            continue;
        }

        if ($file['size'] > 2 * 1024 * 1024) { // 2Mo max
            $errors[$field] = 'Fichier trop volumineux (max 2Mo)';
            continue;
        }

        $uploadedFiles[$field] = $file;
    }

    // Photo optionnelle
    if (!empty($_FILES['field_photo']['name'])) {
        $file = $_FILES['field_photo'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        
        if (in_array($mime, ['image/jpeg', 'image/png']) && $file['size'] <= $maxFileSize) {
            $uploadedFiles['field_photo'] = $file;
        }
    }

    if (!empty($errors)) {
        $response['errors'] = $errors;
        throw new Exception('Données invalides', 422);
    }

    // Connexion DB
    require_once __DIR__ . '/../../../data/dbconn.php';
    $conn->beginTransaction();

    try {
        // Mise à jour agriculteur (téléphone + communes)
        $stmt = $conn->prepare("
            UPDATE agriculteurs 
            SET telephone = ?, communes = ?, date_demande_verification = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $telephone, 
            implode(', ', $communes),
            $agriculteurId
        ]);

        // Création dossier uploads
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            throw new Exception('Erreur création dossier');
        }

        // Enregistrement fichiers
        foreach ($uploadedFiles as $type => $file) {
            $filename = sprintf(
                '%s_%d_%s.%s',
                $type,
                $agriculteurId,
                bin2hex(random_bytes(4)),
                pathinfo($file['name'], PATHINFO_EXTENSION)
            );
            
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                throw new Exception("Erreur enregistrement fichier");
            }

            $docStmt = $conn->prepare("
                INSERT INTO documents_agriculteur 
                (agriculteur_id, type_doc, chemin, date_televersement, statut) 
                VALUES (?, ?, ?, NOW(), 'en_attente')
                ON DUPLICATE KEY UPDATE 
                    chemin = VALUES(chemin),
                    date_televersement = NOW(),
                    statut = 'en_attente'
            ");
            $docStmt->execute([
                $agriculteurId,
                match($type) {
                    'identity' => 'piece_identite',
                    'certificate' => 'certificat_culture',
                    'field_photo' => 'photo_champ'
                },
                $filename
            ]);
        }

        $conn->commit();
        $response = [
            'success' => true,
            'message' => 'Documents et informations enregistrés avec succès',
            'redirect' => 'en_attente_verification.php'
        ];

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    error_log('DB Error: ' . $e->getMessage());
    $response['message'] = 'Erreur base de données';
    http_response_code(500);
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 400);
}

echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
exit;