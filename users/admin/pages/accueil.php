<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    function verifyAuth() {
        if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
            header("Location: authform.php");
            exit;
        }
    }
    verifyAuth();
}

require_once __DIR__ . '/../../../data/dbconn.php';

// Fonctions de données
function getTotalAgriculteurs() {
    global $conn;
    $stmt = $conn->query("SELECT COUNT(*) FROM agriculteurs WHERE compte_active = '1'");
    return $stmt->fetchColumn();
}

function getTotalMarches() {
    global $conn;
    $stmt = $conn->query("SELECT COUNT(*) FROM marches WHERE compte_active = '1'");
    return $stmt->fetchColumn();
}

function getPendingDocuments() {
    global $conn;
    $stmt = $conn->query("SELECT COUNT(*) FROM documents_agriculteur WHERE statut = 'en_attente'");
    return $stmt->fetchColumn();
}

function getRecentActivities($limit = 5) {
    global $conn;
    
    $query = "
        (SELECT 'agriculteur' as type, id, CONCAT(nom, ' ', prenom) as description, 
                date_inscription as date, 'user-plus' as icon 
         FROM agriculteurs 
         ORDER BY date_inscription DESC LIMIT ?)
        
        UNION
        
        (SELECT 'document' as type, d.id, 
                CONCAT('Document envoyé par ', a.nom, ' ', a.prenom) as description, 
                d.date_televersement as date, 'file-upload' as icon 
         FROM documents_agriculteur d
         JOIN agriculteurs a ON d.agriculteur_id = a.id
         ORDER BY d.date_televersement DESC LIMIT ?)
        
        ORDER BY date DESC LIMIT ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRecentAgriculteurs($limit = 5) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT id, nom, prenom, email, date_inscription, compte_verifie 
        FROM agriculteurs 
        wHERE compte_active = '1'
        ORDER BY date_inscription DESC 
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRegistrationStats() {
    global $conn;
    
    $query = "
        SELECT 
            DATE_FORMAT(a.date_inscription, '%Y-%m') AS month,
            COUNT(*) AS agriculteurs,
            (SELECT COUNT(*) FROM marches m WHERE DATE_FORMAT(m.date_inscription, '%Y-%m') = DATE_FORMAT(a.date_inscription, '%Y-%m')) AS marches
        FROM agriculteurs a
        WHERE a.date_inscription >= DATE_SUB(NOW(), INTERVAL 5 MONTH)
        GROUP BY month
        ORDER BY month ASC
    ";
    
    $stmt = $conn->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('d/m/Y à H:i');
}

$stats = getRegistrationStats();
?>
<div class="dashboard-header">
    <div class="header-content">
        <p class="welcome-text">Bienvenue dans l'interface d'administration AgriConnect BENIN</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card stat-primary">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format(getTotalAgriculteurs(), 0, ',', ' ') ?></h3>
            <p>Agriculteurs inscrits</p>
        </div>
        <a href="#" class="stat-link" data-page="agriculteurs">
            Voir détails <i class="fas fa-chevron-right"></i>
        </a>
    </div>

    <div class="stat-card stat-success">
        <div class="stat-icon">
            <i class="fas fa-store"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format(getTotalMarches(), 0, ',', ' ') ?></h3>
            <p>Marchés partenaires</p>
        </div>
        <a href="#" class="stat-link" data-page="marche">
            Voir détails <i class="fas fa-chevron-right"></i>
        </a>
    </div>

    <div class="stat-card stat-warning">
        <div class="stat-icon">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format(getPendingDocuments(), 0, ',', ' ') ?></h3>
            <p>Documents en attente</p>
        </div>
        <a href="#" class="stat-link" data-page="agriculteurs" data-filter="verification">
            Vérifier <i class="fas fa-chevron-right"></i>
        </a>
    </div>
</div>

<!-- Chart Section -->
<div class="chart-section">
    <div class="section-header">
        <h2><i class="fas fa-chart-line"></i> Statistiques des inscriptions</h2>
        <div class="time-filter">
            <button class="active">Ce mois</button>
        </div>
    </div>
    <div class="chart-container">
        <canvas id="registrationsChart"></canvas>
    </div>
</div>

<!-- Main Content -->
<div class="content-grid">
    <!-- Recent Activities -->
    <div class="activities-section">
        <div class="section-header">
            <h2><i class="fas fa-bell"></i> Activités récentes</h2>
            <a href="#" class="view-all"></a>
        </div>
        <div class="activities-list">
            <?php foreach (getRecentActivities(5) as $activity): ?>
            <div class="activity-item">
                <div class="activity-icon activity-<?= $activity['type'] ?>">
                    <i class="fas fa-<?= $activity['icon'] ?>"></i>
                </div>
                <div class="activity-details">
                    <p class="activity-title"><?= htmlspecialchars($activity['description']) ?></p>
                    <span class="activity-time"><?= formatDate($activity['date']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent Farmers -->
    <div class="farmers-section">
        <div class="section-header">
            <h2><i class="fas fa-user-check"></i> Nouveaux agriculteurs</h2>
            <a href="#" class="view-all" data-page="agriculteurs">Voir tout</a>
        </div>
        <div class="farmers-list">
            <?php foreach (getRecentAgriculteurs(3) as $user): ?>
            <div class="farmer-card">
                <div class="farmer-avatar">
                    <?= strtoupper(substr($user['prenom'], 0, 1)) . strtoupper(substr($user['nom'], 0, 1)) ?>
                </div>
                <div class="farmer-info">
                    <h4><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h4>
                    <p><?= htmlspecialchars($user['email']) ?></p>
                    <div class="farmer-meta">
                        <span class="farmer-date"><?= formatDate($user['date_inscription']) ?></span>
                        <span class="farmer-status <?= $user['compte_verifie'] ? 'verified' : 'pending' ?>">
                            <i class="fas fa-<?= $user['compte_verifie'] ? 'check-circle' : 'clock' ?>"></i>
                            <?= $user['compte_verifie'] ? 'Vérifié' : 'En attente' ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
