<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    function verifyAuth() {
    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header("Location: authform.php");
        exit;
    }
}

verifyAuth(); // Empêche l’accès si non admin
}
?>