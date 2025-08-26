<?php
session_start();

// Vérification de la session marché
if (!isset($_SESSION['marche_id']) || !isset($_SESSION['marche_connecte']) || $_SESSION['marche_connecte'] !== true) {
    header("Location: ../../index.php");
    exit();
}

require_once __DIR__ . '/../../data/dbconn.php';

try {
    // Récupération des infos du marché
    $stmt = $conn->prepare("SELECT * FROM marches WHERE id = ?");
    $stmt->execute([$_SESSION['marche_id']]);
    $marche = $stmt->fetch(PDO::FETCH_ASSOC);

    // Messages non lus
    $stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM messages WHERE destinataire_id = ? AND destinataire_type = 'marche' AND lu = 0");
    $stmt->execute([$_SESSION['marche_id']]);
    $unread_messages = $stmt->fetch(PDO::FETCH_ASSOC);

    // Dans la partie où vous récupérez les publications
$commune_filter = isset($_GET['commune']) ? trim(strtolower($_GET['commune'])) : '';
$keyword_filter = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// Requête de base
$sql = "SELECT 
    p.*, 
    a.nom_complet, 
    a.photo_profil,
    a.communes,
    (SELECT COUNT(*) FROM likes WHERE publication_id = p.id) AS nombre_likes,
    (SELECT COUNT(*) FROM likes WHERE publication_id = p.id AND utilisateur_id = ? AND utilisateur_type = 'marche') AS deja_like
FROM publications p
JOIN agriculteurs a ON p.agriculteur_id = a.id
WHERE a.compte_verifie = 1";

$params = [$_SESSION['marche_id']];

// Filtre par commune amélioré
if (!empty($commune_filter)) {
    // Solution 1: Utilisation de LIKE avec des wildcards
    $sql .= " AND LOWER(a.communes) LIKE ?";
    $params[] = '%'.$commune_filter.'%';
}

// Filtre par mot-clé
if (!empty($keyword_filter)) {
    $sql .= " AND (p.contenu LIKE ? OR a.nom_complet LIKE ?)";
    $keyword_param = "%$keyword_filter%";
    $params[] = $keyword_param;
    $params[] = $keyword_param;
}

$sql .= " ORDER BY p.date_publication DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$publications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Marché - AgriConnect BENIN</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="logo">
            <img src="../../assets/images/logo/Logo_back-off.png" height="100%" alt="AgriConnect BENIN">
        </div>

        <div class="search-bar">
            <input type="text" class="search-input" placeholder="Rechercher des agriculteurs ou produits...">
            <button class="search-button">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <ul class="nav-links">
            <li>
                <!-- Chat Button -->
                <a href="chat.php">
                    <div class="nav-icon" id="chatButton">
                    <i class="fas fa-comments"></i>
                    <?php if ($unread_messages['unread_count'] > 0): ?>
                    <span class="chat-badge"><?= $unread_messages['unread_count'] ?></span>
                    <?php endif; ?>
                </div>
                </a>
            </li>
            <li class="profile-dropdown">
                <a href="#" class="profile-link">
                    <img src="assets/images/profiles/user_default_agriculteur.jpg" alt="Photo de profil"
                        class="profile-pic">
                    <span><?= htmlspecialchars(explode(' ', $marche['nom'])[0]) ?></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="profil.php"><i class="fas fa-user"></i> Mon profil</a></li>
                    <li><a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Publications -->
        <div class="publications-container">
            <?php foreach ($publications as $publication): ?>
            <div class="post" data-post-id="<?= $publication['id'] ?>">
                <div class="post-header">
                    <img src="assets/images/profiles/<?= htmlspecialchars($publication['photo_profil']) ?>"
                        alt="Photo de profil">
                    <div class="post-user-info">
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
                        <img src="../agriculteur/assets/images/image_publier/<?= htmlspecialchars($images[0]) ?>"
                            class="post-image" alt="Publication de <?= htmlspecialchars($publication['nom_complet']) ?>"
                            onclick="openLightbox('<?= htmlspecialchars($images[0]) ?>')">
                    </div>

                    <?php elseif ($totalImages === 2): ?>
                    <!-- Affichage côte à côte pour 2 images -->
                    <div class="double-images">
                        <?php foreach ($images as $index => $image): ?>
                        <div class="image-container" style="width: <?= $index === 0 ? '60%' : '40%' ?>">
                            <img src="../agriculteur/assets/images/image_publier/<?= htmlspecialchars($image) ?>"
                                class="post-image"
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
                            <img src="../agriculteur/assets/images/image_publier/<?= htmlspecialchars($image) ?>"
                                class="post-image"
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
                    <div class="post-action like">
                        <i class="<?= $publication['deja_like'] ? 'fas' : 'far' ?> fa-thumbs-up"></i>
                        <span class="like-count" style="color: #2F855A;"><?= $publication['nombre_likes'] ?></span>
                    </div>
                    <div class="post-action contact" style="color: #2F855A;"
                        data-agriculteur-id="<?= $publication['agriculteur_id'] ?>">
                        <i class="bi bi-envelope"></i>
                        <span>Contacter</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Annonces -->
            <div class="announcement-card">
                <h3 class="card-title"><i class="fas fa-bullhorn"></i> Dernières Annonces</h3>

                <div class="announcement-item">
                    <div class="announcement-badge">Nouveau</div>
                    <h4 class="announcement-title">Marché Bio Hebdomadaire</h4>
                    <p class="announcement-content">Tous les samedis de 8h à 14h sur la place du village. Produits frais
                        et locaux garantis.</p>
                    <div class="announcement-meta">
                        <span><i class="far fa-calendar-alt"></i> À partir du 15/06/2024</span>
                    </div>
                </div>

                <div class="announcement-item">
                    <div class="announcement-badge">Important</div>
                    <h4 class="announcement-title">Nouvelle Réglementation</h4>
                    <p class="announcement-content">À compter du 1er juillet, tous les produits doivent être certifiés
                        sans pesticides.</p>
                    <div class="announcement-meta">
                        <span><i class="far fa-clock"></i> Date limite: 30/06/2024</span>
                    </div>
                </div>
            </div>
            <div class="filters-card">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filtres</h3>
                <form action="" method="GET">
        <div class="filter-group">
            <label class="filter-title">Commune</label>
            <select class="filter-select" id="commune-filter" name="commune">
                <option value="">Toutes communes</option>
                <?php
                $communes = [
                            ["name" => "Abomey", "value" => "abomey"],
                            ["name" => "Abomey-Calavi", "value" => "abomey-calavi"],
                            ["name" => "Adjohoun", "value" => "adjohoun"],
                            ["name" => "Adjarra", "value" => "adjarra"],
                            ["name" => "Agbangnizoun", "value" => "agbangnizoun"],
                            ["name" => "Aguégués", "value" => "aguegues"],
                            ["name" => "Allada", "value" => "allada"],
                            ["name" => "Aplahoué", "value" => "aplahoue"],
                            ["name" => "Athiémé", "value" => "athieme"],
                            ["name" => "Avrankou", "value" => "avrankou"],
                            ["name" => "Banikoara", "value" => "banikoara"],
                            ["name" => "Bassila", "value" => "bassila"],
                            ["name" => "Bembèrèkè", "value" => "bembereke"],
                            ["name" => "Bohicon", "value" => "bohicon"],
                            ["name" => "Bopa", "value" => "bopa"],
                            ["name" => "Boukoumbé", "value" => "boukoumbe"],
                            ["name" => "Cotonou", "value" => "cotonou"],
                            ["name" => "Comè", "value" => "come"],
                            ["name" => "Covè", "value" => "cove"],
                            ["name" => "Dassa-Zoumè", "value" => "dassa-zoume"],
                            ["name" => "Djakotomey", "value" => "djakotomey"],
                            ["name" => "Dogbo", "value" => "dogbo"],
                            ["name" => "Grand-Popo", "value" => "grand-popo"],
                            ["name" => "Glazoué", "value" => "glazoue"],
                            ["name" => "Houéyogbé", "value" => "houeyogbe"],
                            ["name" => "Ifangni", "value" => "ifangni"],
                            ["name" => "Kalalè", "value" => "kalale"],
                            ["name" => "Kandi", "value" => "kandi"],
                            ["name" => "Karimama", "value" => "karimama"],
                            ["name" => "Kérou", "value" => "kerou"],
                            ["name" => "Kétou", "value" => "ketou"],
                            ["name" => "Kouandé", "value" => "kouande"],
                            ["name" => "Kpomassè", "value" => "kpomasse"],
                            ["name" => "Lalo", "value" => "lalo"],
                            ["name" => "Lokossa", "value" => "lokossa"],
                            ["name" => "Malanville", "value" => "malanville"],
                            ["name" => "Matéri", "value" => "materi"],
                            ["name" => "Natitingou", "value" => "natitingou"],
                            ["name" => "N'Dali", "value" => "ndali"],
                            ["name" => "Nikki", "value" => "nikki"],
                            ["name" => "Ouaké", "value" => "ouake"],
                            ["name" => "Ouinhi", "value" => "ouinhi"],
                            ["name" => "Ouidah", "value" => "ouidah"],
                            ["name" => "Parakou", "value" => "parakou"],
                            ["name" => "Péhunco", "value" => "pehunco"],
                            ["name" => "Pèrèrè", "value" => "perere"],
                            ["name" => "Pobè", "value" => "pobe"],
                            ["name" => "Porto-Novo", "value" => "porto-novo"],
                            ["name" => "Sakété", "value" => "sakete"],
                            ["name" => "Savalou", "value" => "savalou"],
                            ["name" => "Savè", "value" => "save"],
                            ["name" => "Sèmè-Kpodji", "value" => "seme-kpodji"],
                            ["name" => "So-Ava", "value" => "so-ava"],
                            ["name" => "Tchaourou", "value" => "tchaourou"],
                            ["name" => "Toviklin", "value" => "toviklin"],
                            ["name" => "Tanguiéta", "value" => "tanguieta"],
                            ["name" => "Toukountouna", "value" => "toukountouna"],
                            ["name" => "Toffo", "value" => "toffo"],
                            ["name" => "Zagnanado", "value" => "zagnanado"],
                            ["name" => "Za-Kpota", "value" => "za-kpota"],
                            ["name" => "Zè", "value" => "ze"],
                            ["name" => "Cobly", "value" => "cobly"],
                            ["name" => "Bonou", "value" => "bonou"],
                            ["name" => "Agoué", "value" => "agoue"],
                            ["name" => "Ndali", "value" => "ndali"],
                            ["name" => "Toucountouna", "value" => "toucountouna"],
                            ["name" => "Zakpota", "value" => "zakpota"],
                            ["name" => "Sô-Ava", "value" => "so-ava"],
                            ["name" => "Ouèssè", "value" => "ouesse"]
                        ];
                foreach ($communes as $commune) {
                    $selected = ($commune['value'] === $commune_filter) ? 'selected' : '';
                    echo '<option value="'.htmlspecialchars($commune['value']).'" '.$selected.'>
                        '.htmlspecialchars($commune['name']).'</option>';
                }
                ?>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-title">Mot-clé</label>
            <input type="text" class="filter-select" id="keyword-filter" name="keyword" 
                   placeholder="Produit, agriculteur..." value="<?= htmlspecialchars($keyword_filter) ?>">
        </div>

        <button id="apply-filters" class="filter-button" type="submit">
            <i class="fas fa-search"></i> Appliquer les filtres
        </button>

        <a href="?" id="reset-filters" class="filter-button secondary">
            <i class="fas fa-times"></i> Réinitialiser
        </a>
    </form>
            </div>

        </div>
    </div>


    <script>
        const logo = document.querySelector('.logo');
    if (logo) {
        logo.style.cursor = 'pointer'; // change le curseur pour indiquer que c'est cliquable
        logo.addEventListener('click', function () {
            window.location.href = "index.php"; // redirection au clic
        });
    }

    // Gestion du scroll
    let lastScrollPosition = 0;
    const navbar = document.querySelector('.navbar');
    const scrollThreshold = 100; // Seuil en pixels avant d'appliquer les effets

    window.addEventListener('scroll', function() {
        const currentScrollPosition = window.pageYOffset || document.documentElement.scrollTop;

        // Gestion de l'affichage de la navbar au scroll
        if (currentScrollPosition > lastScrollPosition && currentScrollPosition > scrollThreshold) {
            // Scroll vers le bas - masquer la navbar
            navbar.style.transform = 'translateY(-100%)';
            navbar.style.transition = 'transform 0.3s ease-out';
        } else if (currentScrollPosition < lastScrollPosition) {
            // Scroll vers le haut - afficher la navbar
            navbar.style.transform = 'translateY(0)';
            navbar.style.transition = 'transform 0.3s ease-out';
        }

        // Ajout d'une ombre quand on scroll vers le bas
        if (currentScrollPosition > 10) {
            navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
        } else {
            navbar.style.boxShadow = 'none';
        }

        lastScrollPosition = currentScrollPosition;
    });

    // Scroll doux pour les ancres
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 80, // Compensation pour la navbar
                    behavior: 'smooth'
                });
            }
        });
    });

    // Bouton "Retour en haut"
    const scrollToTopButton = document.createElement('div');
    scrollToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
    scrollToTopButton.style.position = 'fixed';
    scrollToTopButton.style.bottom = '20px';
    scrollToTopButton.style.right = '20px';
    scrollToTopButton.style.width = '50px';
    scrollToTopButton.style.height = '50px';
    scrollToTopButton.style.backgroundColor = 'var(--vert-foret)';
    scrollToTopButton.style.color = 'white';
    scrollToTopButton.style.borderRadius = '50%';
    scrollToTopButton.style.display = 'flex';
    scrollToTopButton.style.alignItems = 'center';
    scrollToTopButton.style.justifyContent = 'center';
    scrollToTopButton.style.cursor = 'pointer';
    scrollToTopButton.style.opacity = '0';
    scrollToTopButton.style.transition = 'opacity 0.3s';
    scrollToTopButton.style.zIndex = '999';
    scrollToTopButton.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    document.body.appendChild(scrollToTopButton);

    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollToTopButton.style.opacity = '1';
        } else {
            scrollToTopButton.style.opacity = '0';
        }
    });

    // Chargement progressif des publications (lazy loading)
    const posts = document.querySelectorAll('.post');
    const options = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, options);

    posts.forEach(post => {
        post.style.opacity = '0';
        post.style.transform = 'translateY(20px)';
        post.style.transition = 'opacity 0.5s, transform 0.5s';
        observer.observe(post);
    });
    </script>
    <script>
    // Gestion du menu déroulant
    document.querySelector('.profile-link').addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector('.dropdown-menu').style.display =
            document.querySelector('.dropdown-menu').style.display === 'block' ? 'none' : 'block';
    });

    // Fermer le menu quand on clique ailleurs
    window.addEventListener('click', function(e) {
        if (!e.target.closest('.profile-dropdown')) {
            document.querySelector('.dropdown-menu').style.display = 'none';
        }
    });

    // Bouton chat
    document.getElementById('chatButton').addEventListener('click', function() {
        window.location.href = 'messagerie.php';
    });

    // Redirection robuste sur le bouton "Contacter"
    document.querySelectorAll('.post-action.contact').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const agriId = this.getAttribute('data-agriculteur-id');
            console.log('Contacter agriId:', agriId);
            if (agriId) {
                window.location.href = `chat.php?contact_id=${agriId}&type=agriculteur`;
            }
        });
    });

    function openLightbox(imageSrc) {
        // Implémentez votre lightbox ici
        console.log("Ouvrir lightbox pour l'image:", imageSrc);
        // Exemple basique:
        const lightbox = document.createElement('div');
        lightbox.style.position = 'fixed';
        lightbox.style.top = '0';
        lightbox.style.left = '0';
        lightbox.style.width = '100%';
        lightbox.style.height = '100%';
        lightbox.style.backgroundColor = 'rgba(0,0,0,0.8)';
        lightbox.style.display = 'flex';
        lightbox.style.alignItems = 'center';
        lightbox.style.justifyContent = 'center';
        lightbox.style.zIndex = '1000';
        lightbox.style.cursor = 'pointer';

        const img = document.createElement('img');
        img.src = '../agriculteur/assets/images/image_publier/' + imageSrc;
        img.style.maxWidth = '90%';
        img.style.maxHeight = '90%';
        img.style.objectFit = 'contain';

        lightbox.appendChild(img);
        lightbox.onclick = function() {
            document.body.removeChild(lightbox);
        };

        document.body.appendChild(lightbox);
    }

    // Système de like amélioré avec statut initial
    document.querySelectorAll('.post-action.like').forEach(btn => {
        // Initialisation de l'état du like
        const icon = btn.querySelector('i');
        const postId = btn.closest('.post').dataset.postId;

        btn.addEventListener('click', async function() {
            const post = this.closest('.post');
            const icon = this.querySelector('i');
            const countSpan = this.querySelector('.like-count');

            // Désactiver le bouton pendant le traitement
            this.disabled = true;
            const originalIcon = icon.className;
            icon.className = 'fas fa-spinner fa-spin';

            const isLiked = originalIcon.includes('fas'); // Vérifie si déjà liké
            const action = isLiked ? 'unlike' : 'like';

            try {
                const response = await fetch('data/traitement_like.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        post_id: postId,
                        action: action,
                        user_type: 'marche',
                        user_id: <?= $_SESSION['marche_id'] ?>
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Erreur serveur');
                }

                // Mise à jour visuelle
                icon.className = action === 'like' ? 'fas fa-thumbs-up' : 'far fa-thumbs-up';
                countSpan.textContent = data.likesCount;

                // Animation de feedback
                post.style.transform = 'scale(1.02)';
                setTimeout(() => post.style.transform = 'scale(1)', 200);

            } catch (error) {
                console.error("Erreur:", error);
                // Réinitialiser l'icône en cas d'erreur
                icon.className = originalIcon;

                // Afficher un message d'erreur
                const errorElement = document.createElement('span');
                errorElement.textContent = 'Erreur, veuillez réessayer';
                errorElement.style.color = 'red';
                errorElement.style.fontSize = '12px';
                errorElement.style.marginLeft = '5px';
                this.appendChild(errorElement);
                setTimeout(() => errorElement.remove(), 3000);

            } finally {
                this.disabled = false;
            }
        });
    });
    </script><
    
</body>

</html>