<?php
session_start();

// Vérification de la session
if (!isset($_SESSION['agriculteur_id']) || !isset($_SESSION['agriculteur_connecte']) || $_SESSION['agriculteur_connecte'] !== true) {
    header("Location: ../../index.php");
    exit();
}

require_once __DIR__ . '/../../data/dbconn.php';

try {
    // Vérification des statuts du compte
    $stmt = $conn->prepare("SELECT compte_active, compte_verifie FROM agriculteurs WHERE id = ?");
    $stmt->execute([$_SESSION['agriculteur_id']]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$account) throw new Exception("Compte introuvable");
    if ($account['compte_active'] != 1) {
        header("Location: ../../identification_agriculteur.php?raison=compte_inactive#showLoginForm");
        exit();
    }
    if ($account['compte_verifie'] != 1) {
        $stmt = $conn->prepare("SELECT SUM(type_doc = 'piece_identite') as has_id, SUM(type_doc = 'certificat_culture') as has_cert FROM documents_agriculteur WHERE agriculteur_id = ?");
        $stmt->execute([$_SESSION['agriculteur_id']]);
        $docs = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$docs['has_id'] || !$docs['has_cert']) {
            $_SESSION['missing_docs'] = ['piece_identite' => !$docs['has_id'], 'certificat_culture' => !$docs['has_cert']];
            header("Location: televersement_docs_agriculteur.php");
            exit();
        }
        header("Location: en_attente_verification.php");
        exit();
    }
    
    // Récupérer les informations de l'agriculteur
    $stmt = $conn->prepare("SELECT nom_complet, photo_profil FROM agriculteurs WHERE id = ?");
    $stmt->execute([$_SESSION['agriculteur_id']]);
    $agriculteur = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Récupérer le nombre de messages non lus
    $stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM messages WHERE destinataire_id = ? AND destinataire_type = 'agriculteur' AND lu = 0");
    $stmt->execute([$_SESSION['agriculteur_id']]);
    $unread_messages = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Récupérer les publications avec les informations de like
$stmt = $conn->prepare("SELECT 
    p.*, 
    a.photo_profil, 
    a.nom_complet,
    (SELECT COUNT(*) FROM likes WHERE publication_id = p.id) AS nombre_likes,
    (SELECT COUNT(*) FROM likes WHERE publication_id = p.id AND utilisateur_id = ? AND utilisateur_type = 'agriculteur') AS user_liked
FROM publications p 
JOIN agriculteurs a ON p.agriculteur_id = a.id 
ORDER BY p.date_publication DESC");
$stmt->execute([$_SESSION['agriculteur_id']]);
$publications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Statistiques de l'agriculteur
    $stmt = $conn->prepare("SELECT 
                           (SELECT COUNT(*) FROM publications WHERE agriculteur_id = ?) as nb_publications,
                           (SELECT COUNT(*) FROM likes WHERE utilisateur_id = ? AND utilisateur_type = 'agriculteur') as nb_likes_donnes,
                           (SELECT COUNT(*) FROM commentaires WHERE auteur_id = ? AND auteur_type = 'agriculteur') as nb_commentaires");
    $stmt->execute([$_SESSION['agriculteur_id'], $_SESSION['agriculteur_id'], $_SESSION['agriculteur_id']]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    // Nombre de marchés ayant contacté cet agriculteur
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT expediteur_id) as nb_marches_contact 
        FROM messages 
        WHERE destinataire_id = ? 
          AND destinataire_type = 'agriculteur' 
          AND expediteur_type = 'marche'");
    $stmt->execute([$_SESSION['agriculteur_id']]);
    $nb_marches_contact = $stmt->fetch(PDO::FETCH_ASSOC)['nb_marches_contact'];
    
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    exit();
} catch (Exception $e) {
    error_log("Application Error: " . $e->getMessage());
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>AgriConnect BENIN</title>
<style>
    .preview-container {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }

    .like-count {
        margin-left: 5px;
        font-size: 0.9em;
        color: #65676b;
    }

    .post-action.like.active {
        color: #2F855A;
    }

    .post-action.like {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .post-action.like .fa-thumbs-up {
        color: <?=$publication['user_liked'] ? '#2F855A': '#65676b'?>;
        transition: color 0.2s;
    }

    .post-action.like:hover .fa-thumbs-up {
        color: #2F855A;
    }

    .like-count {
        margin-left: 3px;
        color: #65676b;
        font-size: 0.9em;
    }

    .preview-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 5px;
        display: block;
    }

    .preview-image-container {
        position: relative;
    }

    .remove-image {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(0, 0, 0, 0.5);
        color: white;
        border: none;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    /* Styles pour la barre de recherche */
    .searchh-bar {
        display: flex;
        align-items: center;
        margin: 0 20px;
        position: relative;
    }

    .searchh-input {
        flex: 1;
        padding: 12px 16px 12px 40px;
        border: 2px solid var(--gris-clair);
        border-radius: 30px;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        color: var(--gris-fonce);
        outline: none;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .searchh-input:focus {
        border-color: var(--vert-foret);
        background-color: white;
        box-shadow: 0 0 0 3px rgba(47, 133, 90, 0.1);
    }

    .searchh-button {
        position: absolute;
        left: 15px;
        bottom: 10px;
        background: none;
        border: none;
        color: var(--gris-doux);
        cursor: pointer;
        transition: color 0.3s;
    }

    .searchh-button:hover {
        color: var(--vert-foret);
    }

    /* Style pour les suggestions de recherche */
    .searchh-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        display: none;
    }

    .searchh-suggestion-item {
        padding: 12px 16px;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .searchh-suggestion-item:hover {
        background-color: var(--fond-clair);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .searchh-bar {
            margin: 0 10px;
            max-width: 300px;
        }

        .searchh-input {
            padding: 10px 16px 10px 36px;
            font-size: 13px;
        }

        .searchh-button {
            left: 12px;
        }
    }

    @media (max-width: 480px) {
        .searchh-bar {
            max-width: 200px;
        }

        .searchh-input {
            padding: 8px 16px 8px 32px;
            font-size: 12px;
        }
    }

    #fileCount {
        margin-left: 10px;
        font-size: 12px;
        color: #65676b;
    }

    .main-container {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        padding: 100px 20px 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .left-column {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .right-column {
        position: sticky;
        top: 80px;
        height: fit-content;
    }



    .create-post {
        background: #fff;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .create-post-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }

    .create-post-header img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .create-post-input {
        background: #f0f2f5;
        border: none;
        border-radius: 20px;
        padding: 10px 15px;
        width: 100%;
        cursor: pointer;
    }

    .create-post-input:hover {
        background: #e4e6e9;
    }

    .post {
        background: #fff;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .post-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .post-header img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .post-user {
        font-weight: 600;
    }

    .post-time {
        font-size: 12px;
        color: #65676b;
    }

    .post-content {
        margin: 10px 0;
    }

    /* Styles pour les images des publications */
    .post-images {
        margin-top: 15px;
        border-radius: 8px;
        overflow: hidden;
    }

    .post-image {
        width: 100%;
        max-height: 500px;
        object-fit: cover;
        cursor: pointer;
        transition: transform 0.3s;
    }

    .post-image:hover {
        transform: scale(1.01);
    }

    .single-image {
        height: 400px;
    }

    .double-images {
        display: flex;
        height: 300px;
    }

    .double-images .image-container {
        height: 100%;
        overflow: hidden;
        object-fit: cover;
    }

    .grid-images {
        display: grid;
        grid-template-columns: 1fr 1fr;
        /* 2 colonnes égales */
        grid-template-rows: auto auto;
        /* 2 lignes */
        gap: 2px;
        height: 300px;
    }

    .grid-item {
        position: relative;
        overflow: hidden;
    }

    .grid-item:nth-child(3){
        grid-column: 1 / span 2;
    }

    .span-two {
        grid-column: span 2;
    }

    .remaining-count {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: bold;
    }

    .post-actions {
        display: flex;
        justify-content: space-around;
        border-top: 1px solid #ddd;
        padding-top: 10px;
        margin-top: 10px;
    }

    .post-action {
        display: flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        padding: 5px 10px;
        border-radius: 5px;
    }

    .post-action:hover {
        background: #f0f2f5;
    }

    .stats-card {
        background: #fff;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .stats-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #1c1e21;
    }

    .stat-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }

    .stat-label {
        color: #65676b;
    }

    .stat-value {
        font-weight: 600;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 2000;
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background: #fff;
        width: 500px;
        max-width: 90%;
        border-radius: 8px;
        overflow: hidden;
    }

    .modal-header {
        padding: 15px;
        border-bottom: 1px solid #ddd;
        text-align: center;
        font-weight: 600;
        position: relative;
    }

    .close-modal {
        position: absolute;
        right: 15px;
        top: 15px;
        cursor: pointer;
        font-size: 20px;
    }

    .modal-body {
        padding: 15px;
    }

    .post-form textarea {
        width: 100%;
        border: none;
        resize: none;
        min-height: 100px;
        font-family: inherit;
        font-size: 16px;
        margin-bottom: 15px;
    }

    .post-form textarea:focus {
        outline: none;
    }

    .post-form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .file-input {
        display: none;
    }

    .file-label {
        padding: 8px 12px;
        background: #f0f2f5;
        border-radius: 5px;
        cursor: pointer;
    }

    .post-button {
        background: #2F855A;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        font-weight: 600;
        cursor: pointer;
    }

    .post-button:hover {
        background: #2F855A;
    }

    .preview-image {
        max-width: 100%;
        max-height: 200px;
        margin-bottom: 15px;
        display: none;
    }

    /* Lightbox styles */
    .lightbox {
        display: none;
        position: fixed;
        z-index: 2000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        justify-content: center;
        align-items: center;
    }

    .lightbox-content {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
    }

    .close-btn {
        position: absolute;
        top: 20px;
        right: 30px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }

    .caption {
        position: absolute;
        bottom: 20px;
        color: white;
        text-align: center;
        width: 100%;
    }

    /* Alert styles */
    .alert {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        z-index: 3000;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        opacity: 1;
        transition: opacity 0.5s ease;
    }

    .alert-success {
        background-color: #4CAF50;
    }

    .alert-error {
        background-color: #f44336;
    }

    .fade-out {
        opacity: 0;
    }

    /* MODIFIEZ ces styles existants */
    .preview-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 5px;
        display: block;
        /* Ajout important */
    }

    .preview-image-container {
        position: relative;
        margin: 5px;
    }

    /* AJOUTEZ ces nouveaux styles */
    #previewContainer {
        min-height: 120px;
        padding: 10px;
        background: #f5f5f5;
        border-radius: 8px;
        margin-top: 10px;
    }

    #fileCount {
        font-weight: bold;
        color: #2F855A;
    }

    .file-input-wrapper {
        position: relative;
        display: inline-block;
    }

    .file-input {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }

    .file-label {
        display: inline-block;
        padding: 8px 12px;
        background: #f0f2f5;
        border-radius: 5px;
        cursor: pointer;
    }
</style>
</head>


<body>
    <nav class="navbar">
        <div class="logo">
            <img src="../../assets/images/logo/Logo_back-off.png" alt="Logo AgriConnect BENIN" height="100%">
        </div>

        <div class="searchh-bar">
            <form action="recherche_publications.php" method="get">
                <input type="text" name="q" placeholder="Rechercher dans les publications..." class="searchh-input">
                <button type="submit" class="searchh-button">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <ul class="nav-links">
            <li>
                <a href="chat.php" class="nav-icon" title="Messages">
                    <i class="fas fa-envelope"></i>
                    <?php if ($unread_messages['unread_count'] > 0): ?>
                    <span class="notification-badge"><?= htmlspecialchars($unread_messages['unread_count']) ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="profile-dropdown">
                <a href="#" class="profile-link">
                    <img src="assets/images/profiles/<?= htmlspecialchars($agriculteur['photo_profil']) ?>"
                        alt="Photo de profil" class="profile-pic" width="30" height="30">
                    <span><?= htmlspecialchars(explode(' ', $agriculteur['nom_complet'])[0]) ?></span>
                    <i class="fas fa-caret-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="profil.php"><i class="fas fa-user"></i> Mon profil</a></li>
                    <li><a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <div class="main-container">
        <div class="left-column">
            <!-- Section pour créer une publication -->
            <div class="create-post">
                <div class="create-post-header">
                    <img src="assets/images/profiles/<?= htmlspecialchars($agriculteur['photo_profil']) ?>"
                        alt="Votre profil">
                    <input type="text" class="create-post-input"
                        placeholder="Quoi de neuf, <?= htmlspecialchars(explode(' ', $agriculteur['nom_complet'])[0]) ?> ?"
                        id="openModal">
                </div>
            </div>

            <!-- Liste des publications -->
            <?php foreach ($publications as $publication): ?>
            <div class="post" data-post-id="<?= htmlspecialchars($publication['id']) ?>">
                <div class="post-header">
                    <img src="assets/images/profiles/<?= htmlspecialchars($publication['photo_profil']) ?>"
                        alt="Profil">
                    <div>
                        <div class="post-user"><?= htmlspecialchars($publication['nom_complet']) ?></div>
                        <div class="post-time"><?= date('d/m/Y H:i', strtotime($publication['date_publication'])) ?>
                        </div>
                    </div>
                </div>
                <div class="post-content">
                    <?= nl2br(htmlspecialchars($publication['contenu'])) ?>
                </div>

                <?php if (!empty($publication['media_chemin'])): ?>
                <div class="post-images">
                    <?php 
                        $images = explode(',', $publication['media_chemin']);
                        $totalImages = count($images);
                        
                        // Affichage différent selon le nombre d'images
                        if ($totalImages === 1): ?>
                    <!-- Affichage simple pour une seule image -->
                    <div class="single-image">
                        <img src="assets/images/image_publier/<?= htmlspecialchars($images[0]) ?>" class="post-image"
                            alt="Publication de <?= htmlspecialchars($publication['nom_complet']) ?>"
                            onclick="openLightbox('<?= htmlspecialchars($images[0]) ?>')">
                    </div>

                    <?php elseif ($totalImages === 2): ?>
                    <!-- Affichage côte à côte pour 2 images -->
                    <div class="double-images">
                        <?php foreach ($images as $index => $image): ?>
                        <div class="image-container" style="width: <?= $index === 0 ? '60%' : '40%' ?>">
                            <img src="assets/images/image_publier/<?= htmlspecialchars($image) ?>" class="post-image"
                                width="150px" height="100%"
                                alt="Publication de <?= htmlspecialchars($publication['nom_complet']) ?>"
                                onclick="openLightbox('<?= htmlspecialchars($image) ?>')">
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <?php elseif ($totalImages >= 3): ?>
                    <!-- Affichage grille pour 3+ images -->
                    <div class="grid-images">
                        <?php foreach ($images as $index => $image): ?>
                        <div class="grid-item <?= $index === 0 && $totalImages > 3 ? 'span-two' : '' ?>">
                            <img src="assets/images/image_publier/<?= htmlspecialchars($image) ?>" class="post-image"
                                alt="Publication de <?= htmlspecialchars($publication['nom_complet']) ?>"
                                onclick="openLightbox('<?= htmlspecialchars($image) ?>')">
                            <?php if ($index === 2 && $totalImages > 3): ?>
                            <div class="remaining-count">+<?= $totalImages - 3 ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="post-actions">
                    <div class="post-action like" style="color: #2F855A;"
                        data-post-id="<?= htmlspecialchars($publication['id']) ?>">
                        <i class="<?= $publication['user_liked'] ? 'fas' : 'far' ?> fa-thumbs-up"></i>
                        <span class="like-text"
                            hidden><?= $publication['user_liked'] ? 'Je n\'aime plus' : 'J\'aime' ?></span>
                        <span class="like-count"><?= htmlspecialchars($publication['nombre_likes']) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="right-column">
            <!-- Statistiques de l'agriculteur -->
            <div class="stats-card">
                <div class="stats-title">Vos statistiques</div>
                <div class="stat-item">
                    <span class="stat-label">Publications</span>
                    <span class="stat-value"><?= htmlspecialchars($stats['nb_publications']) ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Likes totel obtenues</span>
                    <span class="stat-value"><?= htmlspecialchars($stats['nb_likes_donnes']) ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Nombre d'interaction</span>
                    <span class="stat-value"><?= $nb_marches_contact ?></span>
                </div>
            </div>

            <!-- Autres informations -->
            <div class="stats-card">
                <div class="stats-title">Actualités</div>
                <p>Découvrez les nouveaux marchés disponibles cette semaine.</p>
                <p>Formation sur les techniques agricoles modernes le 25 juin.</p>
            </div>
        </div>
    </div>

    <!-- Modal pour créer une publication -->
    <div class="modal" id="postModal">
        <div class="modal-content">
            <div class="modal-header">
                Créer un article
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <form class="post-form" id="newPostForm" enctype="multipart/form-data">
                    <div class="post-header">
                        <img src="assets/images/profiles/<?= htmlspecialchars($agriculteur['photo_profil']) ?>"
                            alt="Profil" width="40" height="40">
                        <span><?= htmlspecialchars($agriculteur['nom_complet']) ?></span>
                    </div>
                    <textarea name="content"
                        placeholder="Quoi de neuf, <?= htmlspecialchars(explode(' ', $agriculteur['nom_complet'])[0]) ?> ?"></textarea>

                    <!-- Zone de prévisualisation des images -->
                    <div class="preview-container" id="previewContainer">
                        <!-- Les prévisualisations apparaîtront ici -->
                    </div>

                    <div class="post-form-actions">
                        <div class="file-input-wrapper">
                            <input type="file" name="media[]" id="fileInput" class="file-input" accept="image/*"
                                multiple>
                            <label for="fileInput" class="file-label">
                                <i class="fas fa-camera"></i> Photos (max 3)
                            </label>
                            <span id="fileCount">0/3</span>
                        </div>
                        <button type="submit" class="post-button">Publier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Lightbox pour l'affichage plein écran -->
    <div id="lightbox" class="lightbox" onclick="closeLightbox()">
        <span class="close-btn">&times;</span>
        <img id="lightbox-img" class="lightbox-content">
        <div class="caption"></div>
    </div>

    <script>
        
        const logo = document.querySelector('.logo');
    if (logo) {
        logo.style.cursor = 'pointer'; // change le curseur pour indiquer que c'est cliquable
        logo.addEventListener('click', function () {
            window.location.href = "index.php"; // redirection au clic
        });
    }
    // Gestion du menu déroulant du profil
    document.querySelector('.profile-link').addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector('.dropdown-menu').classList.toggle('show');
    });

    // Fermer le menu déroulant quand on clique ailleurs
    window.addEventListener('click', function(e) {
        if (!e.target.matches('.profile-link') && !e.target.closest('.profile-link')) {
            const dropdowns = document.querySelectorAll('.dropdown-menu');
            dropdowns.forEach(dropdown => {
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            });
        }
    });

    // Gestion du modal de publication
    const modal = document.getElementById('postModal');
    const btn = document.getElementById('openModal');
    const span = document.querySelector('.close-modal');
    const postForm = document.getElementById('newPostForm');
    const fileInput = document.getElementById('fileInput');
    const previewContainer = document.getElementById('previewContainer');
    const fileCount = document.getElementById('fileCount');

    // Variables pour gérer les fichiers
    let selectedFiles = [];

    // Ouvrir le modal
    btn.onclick = function() {
        modal.style.display = 'flex';
        resetForm();
    }

    // Fermer le modal
    span.onclick = function() {
        modal.style.display = 'none';
    }

    // Fermer le modal en cliquant à l'extérieur
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    // Remplacer la gestion actuelle du fileInput par ceci :
    fileInput.addEventListener('change', function(e) {
        const newFiles = Array.from(e.target.files);

        // Vérifier le nombre total de fichiers
        if (selectedFiles.length + newFiles.length > 3) {
            alert('Vous ne pouvez sélectionner que 3 photos maximum');
            return;
        }

        // Vérification de la taille et du type
        for (const file of newFiles) {
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Seuls les fichiers JPEG, PNG, GIF et WebP sont autorisés');
                return;
            }

            if (file.size > 5 * 1024 * 1024) { // 5MB max
                alert('La taille d\'un fichier ne doit pas dépasser 5MB');
                return;
            }
        }

        // Ajouter les nouveaux fichiers aux existants
        selectedFiles = [...selectedFiles, ...newFiles];
        updateFileDisplay();

        // Mettre à jour l'input file (nécessaire pour la soumission du formulaire)
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
    });

    // Dans la fonction updateFileDisplay(), modifiez comme suit :
    function updateFileDisplay() {
        previewContainer.innerHTML = '';
        fileCount.textContent = `${selectedFiles.length}/3`;

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();

            reader.onload = function(event) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'preview-image-container';
                previewDiv.dataset.index = index;

                const img = document.createElement('img');
                img.src = event.target.result;
                img.className = 'preview-image';

                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-image';
                removeBtn.innerHTML = '&times;';
                removeBtn.onclick = function() {
                    removeFile(index);
                };

                previewDiv.appendChild(img);
                previewDiv.appendChild(removeBtn);
                previewContainer.appendChild(previewDiv);
            };

            reader.readAsDataURL(file);
        });
    }

    // Supprimer un fichier
    function removeFile(index) {
        selectedFiles.splice(index, 1);
        updateFileDisplay();

        // Mise à jour de l'input file
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
        fileCount.textContent = `${selectedFiles.length}/3`;
    }

    // Réinitialiser l'input file
    function resetFileInput() {
        fileInput.value = '';
        selectedFiles = [];
        fileCount.textContent = '0/3';
        previewContainer.innerHTML = '';

        // Réinitialiser l'input file
        const dataTransfer = new DataTransfer();
        fileInput.files = dataTransfer.files;
    }

    // Réinitialiser tout le formulaire
    function resetForm() {
        postForm.reset();
        resetFileInput();
    }

    // Envoi du formulaire
    postForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const content = postForm.content.value.trim();
        const files = selectedFiles;

        // Validation basique
        if (content === '' && files.length === 0) {
            showErrorMessage('Veuillez ajouter du texte ou au moins une photo');
            return;
        }

        // Afficher un indicateur de chargement
        const submitBtn = postForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publication...';

        try {
            // Préparation des données
            const formData = new FormData();
            formData.append('content', content);
            formData.append('agriculteur_id', <?= $_SESSION['agriculteur_id'] ?>);

            // Ajout des fichiers (important: utiliser l'index pour chaque fichier)
            for (let i = 0; i < files.length; i++) {
                formData.append('media[]', files[i]);
            }

            // Envoi AJAX
            const response = await fetch('data/traitement_publication.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Succès - fermer le modal et recharger les publications
                modal.style.display = 'none';
                showSuccessMessage(data.message || 'Publication créée avec succès!');
                setTimeout(() => location.reload(), 1000);
            } else {
                // Erreur
                showErrorMessage(data.message || 'Erreur lors de la publication');
            }
        } catch (error) {
            console.error('Error:', error);
            showErrorMessage('Une erreur réseau est survenue');
        } finally {
            // Réactiver le bouton
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    });
    // Lightbox functions
    function openLightbox(imageSrc) {
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');

        lightboxImg.src = 'assets/images/image_publier/' + imageSrc;
        lightbox.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        document.getElementById('lightbox').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Fermer la lightbox avec ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLightbox();
        }
    });

    // Afficher un message de succès
    function showSuccessMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success';
        alertDiv.textContent = message;

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            alertDiv.classList.add('fade-out');
            setTimeout(() => alertDiv.remove(), 500);
        }, 3000);
    }

    // Afficher un message d'erreur
    function showErrorMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-error';
        alertDiv.textContent = message;

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            alertDiv.classList.add('fade-out');
            setTimeout(() => alertDiv.remove(), 500);
        }, 3000);
    }

    // Gestion des likes
    document.addEventListener('click', function(e) {
        if (e.target.closest('.post-action.like')) {
            const postAction = e.target.closest('.post-action');
            const postId = postAction.closest('.post').dataset.postId;
            toggleLike(postId, postAction);
        }
    });

    // Fonction pour liker/unliker
    async function toggleLike(postId, element) {
        const icon = element.querySelector('i');
        const likeText = element.querySelector('.like-text');
        const likeCount = element.querySelector('.like-count');
        const isLiked = icon.classList.contains('fas');
        const action = isLiked ? 'unlike' : 'like';

        // Sauvegarde de l'état original
        const originalState = {
            iconClass: icon.className,
            text: likeText.textContent,
            count: likeCount.textContent
        };

        // Mise à jour visuelle immédiate
        icon.classList.toggle('fas', !isLiked);
        icon.classList.toggle('far', isLiked);
        likeText.textContent = isLiked ? 'J\'aime' : 'Je n\'aime plus';

        try {
            const response = await fetch('data/traitement_like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    post_id: postId,
                    action: action,
                    user_type: 'agriculteur',
                    user_id: <?= $_SESSION['agriculteur_id'] ?>
                })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Erreur lors du like');
            }

            // Mise à jour du compteur
            likeCount.textContent = data.likesCount;

        } catch (error) {
            // Annulation des changements en cas d'erreur
            icon.className = originalState.iconClass;
            likeText.textContent = originalState.text;
            likeCount.textContent = originalState.count;

            console.error('Erreur:', error);
            showErrorMessage('Une erreur est survenue');
        }
    }
    </script>
</body>

</html>