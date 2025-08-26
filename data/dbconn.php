<?php
date_default_timezone_set('Africa/Porto-Novo');

$host = 'localhost';
$dbname = 'agriconnectbenin';
$username = 'root';
$password = '';

// Options PDO recommandées
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Erreurs en exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Résultats en tableau associatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Requêtes préparées réelles
];

try {
    // Connexion à la base avec PDO + options
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, $options);

    // Régler le fuseau horaire MySQL (pour CURRENT_TIMESTAMP, NOW(), etc.)
    $conn->exec("SET time_zone = '+01:00'");

} catch (PDOException $e) {
    die("Connexion à la base de données échouée : " . $e->getMessage());
}
?>
