<?php
// data/telecharger.php

if (!isset($_GET['file'])) {
    http_response_code(400);
    exit('Fichier non spécifié');
}

$filename = basename($_GET['file']);
$filepath = __DIR__ . '/../../agriculteur/uploads/' . $filename;

if (!file_exists($filepath)) {
    http_response_code(404);
    exit('Fichier introuvable');
}

// Forcer le téléchargement
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
