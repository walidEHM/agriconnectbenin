<?php
// Vérification de session et sécurité
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

// Au début du script
ini_set('display_errors', 0);
error_reporting(0);
require_once __DIR__ . '/../../../data/dbconn.php';

function getRecentAgriculteurs($limit = 5) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT *
        FROM agriculteurs
        ORDER BY date_inscription DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAgriculteursEnAttente() {
    global $conn;
    $stmt = $conn->prepare("
        SELECT a.id, a.nom, a.prenom, a.nom_complet, a.email, a.telephone,
               a.date_demande_verification, a.date_inscription,
               COUNT(d.id) as nb_documents
        FROM agriculteurs a
        LEFT JOIN documents_agriculteur d ON a.id = d.agriculteur_id
        WHERE a.date_demande_verification IS NOT NULL
        AND a.compte_verifie = 0
        GROUP BY a.id
        ORDER BY a.date_demande_verification ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAgriculteurDetails($id) {
    global $conn;
    try {
        // Récupération des infos de l'agriculteur
$stmt = $conn->prepare("
    SELECT id, nom, prenom, nom_complet, email, telephone, 
           date_inscription, compte_verifie, date_demande_verification
    FROM agriculteurs
    WHERE id = ?
");
$stmt->execute([$id]);
$agriculteur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$agriculteur) {
    throw new Exception('Agriculteur non trouvé');
}

// Récupération des documents associés
$stmtDocs = $conn->prepare("
    SELECT id, type_doc, chemin, date_televersement, statut
    FROM documents_agriculteur
    WHERE agriculteur_id = ?
    AND type_doc IN ('piece_identite', 'certificat_culture', 'photo_champ')
    AND chemin IS NOT NULL
");
$stmtDocs->execute([$id]);
$documents = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

// Vérification des documents obligatoires
$hasRequiredDocs = count(array_filter($documents, fn($doc) =>
    in_array($doc['type_doc'], ['piece_identite', 'certificat_culture'])
)) >= 2;

// Retour du résultat
return [
    'agriculteur' => $agriculteur,
    'documents' => $documents,
    'hasRequiredDocs' => $hasRequiredDocs
];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function verifierAgriculteur($id) {
    global $conn;
    try {
        $conn->beginTransaction();
        
        $stmtCheck = $conn->prepare("
            SELECT COUNT(*) 
            FROM documents_agriculteur 
            WHERE agriculteur_id = ? 
            AND type_doc IN ('piece_identite', 'certificat_culture')
        ");
        $stmtCheck->execute([$id]);
        
        if ($stmtCheck->fetchColumn() < 2) {
            return ['success' => false, 'error' => 'Documents obligatoires manquants'];
        }
        
        $conn->prepare("UPDATE agriculteurs SET compte_verifie = 1, date_demande_verification = NULL WHERE id = ?")
             ->execute([$id]);
        
        $conn->prepare("UPDATE documents_agriculteur SET statut = 'approuve' WHERE agriculteur_id = ?")
             ->execute([$id]);
        
        $conn->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $conn->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Gestion des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (!isset($_POST['action'])) {
        echo json_encode(['success' => false, 'error' => 'Action non spécifiée']);
        exit;
    }

    switch ($_POST['action']) {
        case 'verifier':
            if (isset($_POST['id'])) {
                echo json_encode(verifierAgriculteur($_POST['id']));
                exit;
            }
            break;
            
        case 'get_details':
            if (isset($_POST['id'])) {
                echo json_encode(getAgriculteurDetails($_POST['id']));
                exit;
            }
            break;
    }
    
    echo json_encode(['success' => false, 'error' => 'Action non reconnue']);
    exit;
}

// Récupération des données
$recentAgriculteurs = getRecentAgriculteurs();
$agriculteursEnAttente = getAgriculteursEnAttente();

function formatDate($dateString) {
    return $dateString ? (new DateTime($dateString))->format('d/m/Y H:i') : 'N/A';
}

function getDocumentTypeName($type) {
    return match($type) {
        'piece_identite' => 'Pièce d\'identité',
        'certificat_culture' => 'Certificat de culture',
        'photo_champ' => 'Photo du champ (facultative)',
        default => $type
    };
}

?>

<div class="dashboard-header">
    <div class="header-content">
        <p class="welcome-text">Tableau de bord de gestion des agriculteurs</p>
    </div>
</div>

<!-- Section 1: Derniers agriculteurs inscrits -->
<div class="section-card">
    <div class="section-header">
        <h2><i class="fas fa-users"></i> Derniers agriculteurs inscrits</h2>
        <a href="#" class="view-all" data-page="agriculteurs">Voir tout</a>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nom complet</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Inscription</th>
                    <th>Statut</th>
                    <th>Compte active</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentAgriculteurs)): ?>
                <tr>
                    <td colspan="6" class="no-data"><i class="fas fa-info-circle"></i> Aucun agriculteur récent</td>
                </tr>
                <?php else: ?>
                <?php foreach ($recentAgriculteurs as $agriculteur): ?>
                <tr>
                    <td><?= htmlspecialchars($agriculteur['nom_complet']) ?></td>
                    <td><?= htmlspecialchars($agriculteur['email']) ?></td>
                    <td><?= htmlspecialchars($agriculteur['telephone'] ?? 'N/A') ?></td>
                    <td><?= formatDate($agriculteur['date_inscription']) ?></td>
                    <td>
                        <span class="status-badge <?= $agriculteur['compte_verifie'] ? 'verified' : 'pending' ?>">
                            <i class="fas fa-<?= $agriculteur['compte_verifie'] ? 'check-circle' : 'clock' ?>"></i>
                            <?= $agriculteur['compte_verifie'] ? 'Vérifié' : 'Non vérifié' ?>
                        </span>
                    </td>
                    <td style="text-align: center ; text-align: center;  ">
                        <?= htmlspecialchars($agriculteur['compte_active']) ?></td>

                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Section 2: Agriculteurs en attente de vérification -->
<div class="section-card">
    <div class="section-header">
        <h2><i class="fas fa-user-clock"></i> Demandes de vérification</h2>
        <span class="badge-count"><?= count($agriculteursEnAttente) ?></span>
    </div>

    <?php if (empty($agriculteursEnAttente)): ?>
    <div class="no-data-message"><i class="fas fa-check-circle"></i> Aucune demande de vérification en attente</div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="data-table dropdown-table">
            <thead>
                <tr>
                    <th>Nom complet</th>
                    <th>Contact</th>
                    <th>Documents</th>
                    <th>Demande le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agriculteursEnAttente as $agriculteur): ?>
                <tr class="dropdown-row">
                    <td>
                        <div class="user-info">
                            <div class="user-avatar">
                                <?= strtoupper(substr($agriculteur['prenom'], 0, 1)) . strtoupper(substr($agriculteur['nom'], 0, 1)) ?>
                            </div>
                            <div class="user-details">
                                <strong><?= htmlspecialchars($agriculteur['nom_complet']) ?></strong>
                                <small>ID: <?= $agriculteur['id'] ?></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div><?= htmlspecialchars($agriculteur['email']) ?></div>
                        <small><?= htmlspecialchars($agriculteur['telephone'] ?? 'Tél. non renseigné') ?></small>
                    </td>
                    <td>
                        <span class="documents-count">
                            <i class="fas fa-file-alt"></i> <?= $agriculteur['nb_documents'] ?> doc(s)
                        </span>
                    </td>
                    <td><?= formatDate($agriculteur['date_demande_verification']) ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-action btn-view-dropdown" data-id="<?= $agriculteur['id'] ?>">
                                <i class="fas fa-eye"></i> Voir
                            </button>
                            <button class="btn-action btn-verify" data-id="<?= $agriculteur['id'] ?>">
                                <i class="fas fa-check"></i> Valider
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="dropdown-content" id="dropdown-<?= $agriculteur['id'] ?>">
                    <td colspan="5">
                        <div class="dropdown-details">
                            <div class="loading-content">
                                <i class="fas fa-spinner fa-spin"></i> Chargement des détails...
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>



<style>
.section-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    padding: 25px;
    margin-bottom: 25px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-header h2 {
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--gris-fonce);
}

.badge-count {
    background: var(--orange-terre);
    color: white;
    padding: 5px 10px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid var(--gris-clair);
}

.data-table tr:hover td {
    background-color: rgba(47, 133, 90, 0.03);
}

.dropdown-content {
    display: none;
    background-color: #f9f9f9;
}

.dropdown-details {
    padding: 20px;
}

.no-data,
.no-data-message {
    text-align: center;
    padding: 30px;
    color: var(--gris-doux);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-badge.verified {
    background-color: rgba(56, 161, 105, 0.1);
    color: var(--vert-vif);
}

.status-badge.pending {
    background-color: rgba(237, 137, 54, 0.1);
    color: var(--orange-terre);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background-color: var(--vert-foret);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-action {
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    transition: var(--transition);
    border: none;
}

.btn-view,
.btn-view-dropdown {
    background: var(--gris-clair);
    color: var(--gris-fonce);
}

.btn-verify {
    background: var(--vert-foret);
    color: white;
}

/* Dropdown Details Styles */
.user-details-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.detail-group {
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.detail-group h4 {
    margin-top: 0;
    margin-bottom: 15px;
    color: var(--vert-foret);
    font-size: 1rem;
}

.detail-row {
    display: flex;
    margin-bottom: 10px;
}

.detail-row label {
    font-weight: 600;
    width: 150px;
    color: var(--gris-fonce);
}

.documents-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.document-item {
    display: flex;
    align-items: center;
    padding: 10px;
    background: #f5f5f5;
    border-radius: 6px;
    gap: 10px;
}

.document-info {
    flex-grow: 1;
}

.document-name {
    font-weight: 500;
    display: block;
}

.document-status {
    font-size: 0.7rem;
    padding: 2px 6px;
    border-radius: 4px;
    background: #fff3cd;
    color: #856404;
}

.btn-download {
    color: var(--vert-foret);
    padding: 5px;
}

.documents-warning {
    background: #fff3cd;
    color: #856404;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.verification-actions {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px dashed #ddd;
}

.btn-verify-dropdown {
    background: var(--vert-foret);
    color: white;
    padding: 8px 15px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
}

.verification-hint {
    color: var(--orange-terre);
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.loading-content {
    text-align: center;
    padding: 20px;
    color: var(--gris-doux);
}

.error-message {
    text-align: center;
    padding: 20px;
    color: #dc3545;
}

.btn-retry {
    background: var(--gris-clair);
    color: var(--gris-fonce);
    padding: 8px 15px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    margin-top: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .action-buttons {
        flex-direction: column;
    }

    .user-details-content {
        grid-template-columns: 1fr;
    }

    .detail-row {
        flex-direction: column;
    }

    .detail-row label {
        width: auto;
        margin-bottom: 3px;
    }
}
</style>