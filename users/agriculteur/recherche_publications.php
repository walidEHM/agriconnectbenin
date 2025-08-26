<?php
session_start();

// Vérification de la session
if (!isset($_SESSION['agriculteur_id']) || !isset($_SESSION['agriculteur_connecte']) || $_SESSION['agriculteur_connecte'] !== true) {
    header("Location: ../../index.php");
    exit();
}

require_once __DIR__ . '/../../data/dbconn.php';

// Récupérer le terme de recherche
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    // Recherche dans les publications
    $stmt = $conn->prepare("SELECT p.*, a.photo_profil, a.nom_complet 
                           FROM publications p 
                           JOIN agriculteurs a ON p.agriculteur_id = a.id 
                           WHERE p.contenu LIKE :search
                           ORDER BY p.date_publication DESC");
    $stmt->execute([':search' => '%' . $searchTerm . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $results = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche - AgriConnect BENIN</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --vert-foret: #2F855A;
            --orange-terre: #ED8936;
            --gris-doux: #718096;
            --fond-clair: #F0FFF4;
            --vert-vif: #38A169;
            --rouge: #E53E3E;
            --gris-fonce: #2D3748;
            --gris-clair: #E2E8F0;
        }
        
        
        .back-button {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background-color: var(--vert-foret);
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(47, 133, 90, 0.2);
        }
        
        .back-button:hover {
            background-color: #276749;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(47, 133, 90, 0.3);
        }
        
        .back-button i {
            margin-right: 8px;
        }
        
        .main-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            padding: 100px 20px 40px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .search-header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .search-title {
            font-size: 24px;
            color: var(--vert-foret);
            margin-bottom: 5px;
        }
        
        .search-term {
            color: var(--orange-terre);
            font-weight: 600;
            background: rgba(237, 137, 54, 0.1);
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-block;
        }
        
        .result-count {
            color: var(--gris-doux);
            font-size: 14px;
            font-weight: 500;
        }
        
        .post {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .post:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .post-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .post-header img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid var(--gris-clair);
        }
        
        .post-user {
            font-weight: 600;
            color: var(--gris-fonce);
        }
        
        .post-time {
            font-size: 13px;
            color: var(--gris-doux);
        }
        
        .post-content {
            margin: 15px 0;
            line-height: 1.6;
            color: var(--gris-fonce);
        }
        
        .highlight {
            background-color: rgba(255, 235, 59, 0.5);
            padding: 0 2px;
            border-radius: 3px;
            font-weight: 500;
        }
        
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
            border-radius: 8px;
        }
        
        .post-image:hover {
            transform: scale(1.02);
        }
        
        .post-actions {
            display: flex;
            justify-content: space-around;
            border-top: 1px solid var(--gris-clair);
            padding-top: 15px;
            margin-top: 15px;
        }
        
        .post-action {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 8px;
            color: var(--gris-doux);
            transition: all 0.2s ease;
        }
        
        .post-action:hover {
            background-color: var(--fond-clair);
            color: var(--vert-foret);
        }
        
        .post-action i {
            font-size: 18px;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .no-results i {
            font-size: 60px;
            color: var(--orange-terre);
            margin-bottom: 20px;
            opacity: 0.7;
        }
        
        .no-results h3 {
            color: var(--gris-fonce);
            margin-bottom: 10px;
        }
        
        .no-results p {
            color: var(--gris-doux);
            max-width: 400px;
            margin: 0 auto 20px;
        }
        
        @media (max-width: 768px) {
            .main-container {
                padding: 90px 15px 30px;
                grid-template-columns: 1fr;
            }
            
            .post {
                padding: 15px;
            }
            
            .post-header img {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="../../assets/images/logo/Logo_back-off.png" alt="Logo AgriConnect BENIN" height="100%">
        </div>
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </nav>

    <div class="main-container">
        <div class="search-header">
            <h1 class="search-title">Résultats de recherche</h1>
            <p>Pour le terme : <span class="search-term"><?= htmlspecialchars($searchTerm) ?></span></p>
            <div class="result-count"><?= count($results) ?> publication(s) correspondante(s)</div>
        </div>

        <?php if (empty($results)): ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>Aucun résultat trouvé</h3>
                <p>Votre recherche pour "<?= htmlspecialchars($searchTerm) ?>" n'a retourné aucun résultat. Essayez avec d'autres termes.</p>
                <a href="accueil_agriculteur.php" class="back-button">
                    <i class="fas fa-home"></i> Retour à l'accueil
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($results as $publication): ?>
                <div class="post">
                    <div class="post-header">
                        <img src="assets/images/profiles/<?= htmlspecialchars($publication['photo_profil']) ?>" alt="Profil">
                        <div>
                            <div class="post-user"><?= htmlspecialchars($publication['nom_complet']) ?></div>
                            <div class="post-time"><?= date('d/m/Y à H:i', strtotime($publication['date_publication'])) ?></div>
                        </div>
                    </div>
                    <div class="post-content">
                        <?= highlightSearchTerm(nl2br(htmlspecialchars($publication['contenu'])), $searchTerm) ?>
                    </div>
                    
                    <?php if (!empty($publication['media_chemin'])): ?>
                        <div class="post-images">
                            <?php 
                                $images = explode(',', $publication['media_chemin']);
                                $totalImages = count($images);
                                
                                if ($totalImages === 1): ?>
                                <!-- Affichage simple pour une seule image -->
                                <img src="assets/images/image_publier/<?= htmlspecialchars($images[0]) ?>" class="post-image"
                                    alt="Publication de <?= htmlspecialchars($publication['nom_complet']) ?>">
                            <?php elseif ($totalImages === 2): ?>
                                <!-- Affichage côte à côte pour 2 images -->
                                <div class="double-images">
                                    <?php foreach ($images as $index => $image): ?>
                                    <div class="image-container" style="width: <?= $index === 0 ? '60%' : '40%' ?>">
                                        <img src="assets/images/image_publier/<?= htmlspecialchars($image) ?>" class="post-image"
                                            alt="Publication de <?= htmlspecialchars($publication['nom_complet']) ?>">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif ($totalImages >= 3): ?>
                                <!-- Affichage grille pour 3+ images -->
                                <div class="grid-images">
                                    <?php foreach ($images as $index => $image): ?>
                                    <div class="grid-item <?= $index === 0 && $totalImages > 3 ? 'span-two' : '' ?>">
                                        <img src="assets/images/image_publier/<?= htmlspecialchars($image) ?>" class="post-image"
                                            alt="Publication de <?= htmlspecialchars($publication['nom_complet']) ?>">
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
                        <div class="post-action like">
                            <i class="far fa-thumbs-up"></i> <span>J'aime</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php
    // Fonction pour surligner le terme de recherche dans le texte
    function highlightSearchTerm($text, $term) {
        if (empty($term)) return $text;
        
        $pattern = '/(' . preg_quote($term, '/') . ')/i';
        return preg_replace($pattern, '<span class="highlight">$1</span>', $text);
    }
    ?>
</body>
</html>