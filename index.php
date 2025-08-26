<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Plateforme de mise en relation entre agriculteurs et acheteurs au Bénin">
    <meta name="keywords" content="Agriculture, Bénin, Produit local, Agriculteurs, Marché local">
    <meta property="og:title" content="AgriConnect BENIN - Relions Agriculteurs et Marchés">
    <meta property="og:description"
        content="Plateforme pour connecter les agriculteurs béninois aux marchés locaux et nationaux.">
    <meta name="robots" content="index, follow">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="icon" href="assets/images/logo/Logo_back.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <title>AgriConnect BENIN - Accueil</title>
</head>

<body>
    <header class="hero-header">
        <nav class="navbar">
            <div class="logo">
                <img src="assets/images/logo/Logo_back-off.png" alt="Logo AgriConnect BENIN" height="100%">
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="#products">Produits</a></li>
                <li><a href="#features">Fonctionnalités</a></li>
                <li><a href="#about">À propos</a></li>
                <li>
                    <a href="#inscription" class="btn btn-outline">
                        <i class="fa fa-user-plus"></i> S'inscrire
                    </a>
                </li>
                <li>
                    <a href="#" class="btn btn-primary" onclick="openLoginModal()">
                        <i class="fa fa-sign-in-alt"></i> Connexion
                    </a>
                </li>
            </ul>
        </nav>
        <div class="hero-banner">
            <div class="hero-content">
                <h1>AgriConnect BENIN</h1>
                <h2>La passerelle entre agriculteurs et marchés locaux au Bénin.</h2>
                <div class="typewriter-container">
                    <span id="typewriterText" class="typewriter-text"></span>
                </div>
            </div>
        </div>
    </header>

    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeLoginModal()">&times;</span>
            <h2>Se connecter à AgriConnect</h2>
            <p class="modal-subtitle">Choisissez votre profil pour continuer</p>

            <div class="login-choice">
                <div class="choice-card" onclick="location.href='identification_agriculteur.php#showLoginForm'">
                    <i class="fas fa-tractor"></i>
                    <h3>Agriculteur</h3>
                    <p>Connectez-vous pour gérer vos produits</p>
                    <div class="card-hover"></div>
                </div>

                <div class="choice-card" onclick="location.href='identification_marche.php#showLoginForm'">
                    <i class="fas fa-store"></i>
                    <h3>Marché/Acheteur</h3>
                    <p>Accédez aux produits locaux</p>
                    <div class="card-hover"></div>
                </div>
            </div>

            <p class="modal-footer">Nouveau sur AgriConnect? <a href="#inscription" onclick="closeLoginModal()">Créer un
                    compte</a></p>
        </div>
    </div>

    <section id="products" class="products-showcase">
        <div class="container">
            <h2 class="section-title">Les Produits Locaux</h2>
            <p class="section-subtitle">Directement des agriculteurs de votre région</p>

            <!-- Navigation par catégories -->
            <div class="category-tabs" data-aos="fade-up">
                <button class="tab-btn active" data-category="cereales">Céréales</button>
                <button class="tab-btn" data-category="legumes">Légumes</button>
                <button class="tab-btn" data-category="fruits">Fruits</button>
                <button class="tab-btn" data-category="tubercules">Tubercules</button>
            </div>

            <!-- Contenu des onglets -->
            <div class="tab-content">
                <!-- Onglet Céréales -->
                <div class="tab-pane active" id="cereales">
                    <div class="product-grid">
                        <!-- Produit 1 -->
                        <div class="product-card" data-aos="fade-up">
                            <div class="product-image"
                                style="background-image: url('assets/images/produits/maiis.png');"></div>
                            <div class="product-content">
                                <h3>Maïs Local</h3>
                                <p class="product-description">Maïs jaune de qualité premium</p>
                                <div class="product-meta">
                                    <span class="product-location"><i class="fas fa-map-marker-alt"></i> Parakou</span>
                                </div>
                            </div>
                        </div>

                        <!-- Produit 2 -->
                        <div class="product-card" data-aos="fade-up" data-aos-delay="100">
                            <div class="product-image" style="background-image: url('assets/images/produits/riz.png');">
                            </div>
                            <div class="product-content">
                                <h3>Riz de la Vallée</h3>
                                <p class="product-description">Riz blanc parfumé</p>
                                <div class="product-meta">
                                    <span class="product-location"><i class="fas fa-map-marker-alt"></i>
                                        Malanville</span>
                                </div>
                            </div>
                        </div>

                        <!-- Produit 3 -->
                        <div class="product-card" data-aos="fade-up" data-aos-delay="200">
                            <div class="product-image"
                                style="background-image: url('assets/images/produits/sorgho.png');"></div>
                            <div class="product-content">
                                <h3>Sorgho Rouge</h3>
                                <p class="product-description">Sorgho riche en nutriments</p>
                                <div class="product-meta">
                                    <span class="product-location"><i class="fas fa-map-marker-alt"></i>
                                        Natitingou</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglet Légumes -->
                <div class="tab-pane" id="legumes">
                    <div class="product-grid">
                        <!-- Produit 1 -->
                        <div class="product-card" data-aos="fade-up">
                            <div class="product-image"
                                style="background-image: url('assets/images/produits/tomates.png');"></div>
                            <div class="product-content">
                                <h3>Tomates Fraîches</h3>
                                <p class="product-description">Tomates rouges juteuses</p>
                                <div class="product-meta">
                                    <span class="product-location"><i class="fas fa-map-marker-alt"></i> Kétou</span>
                                </div>
                            </div>
                        </div>

                        <!-- Produit 2 -->
                        <div class="product-card" data-aos="fade-up" data-aos-delay="100">
                            <div class="product-image"
                                style="background-image: url('assets/images/produits/piments.png');"></div>
                            <div class="product-content">
                                <h3>Piments Locaux</h3>
                                <p class="product-description">Piments forts et aromatiques</p>
                                <div class="product-meta">
                                    <span class="product-location"><i class="fas fa-map-marker-alt"></i> Bohicon</span>
                                </div>
                            </div>
                        </div>

                        <!-- Produit 3 -->
                        <div class="product-card" data-aos="fade-up" data-aos-delay="200">
                            <div class="product-image"
                                style="background-image: url('assets/images/produits/aubergines.png');"></div>
                            <div class="product-content">
                                <h3>Aubergines</h3>
                                <p class="product-description">Aubergines fraîches du jardin</p>
                                <div class="product-meta">
                                    <span class="product-location"><i class="fas fa-map-marker-alt"></i> Dassa</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglet Fruits -->
                <div class="tab-pane" id="fruits">
                    <div class="product-grid">
                        <!-- Produit 1 -->
                        <div class="product-card" data-aos="fade-up">
                            <div class="product-image"
                                style="background-image: url('assets/images/produits/oranges.jpg');"></div>
                            <div class="product-content">
                                <h3>Tomates Fraîches</h3>
                                <p class="product-description">Tomates rouges juteuses</p>
                                <div class="product-meta">
                                    <span class="product-location"><i class="fas fa-map-marker-alt"></i> Kétou</span>
                                </div>
                            </div>
                        </div>

                        <!-- Produit 2 -->
                        <div class="product-card" data-aos="fade-up" data-aos-delay="100">
                            <div class="product-image"
                                style="background-image: url('assets/images/produits/ananas.png');"></div>
                            <div class="product-content">
                                <h3>Piments Locaux</h3>
                                <p class="product-description">Piments forts et aromatiques</p>
                                <div class="product-meta">
                                    <span class="product-location"><i class="fas fa-map-marker-alt"></i> Bohicon</span>
                                </div>
                            </div>
                        </div>

                        <!-- Produit 3 -->
                        <div class="product-card" data-aos="fade-up" data-aos-delay="200">
                            <div class="product-image"
                                style="background-image: url('assets/images/produits/pasteques.png');"></div>
                            <div class="product-content">
                                <h3>Aubergines</h3>
                                <p class="product-description">Aubergines fraîches du jardin</p>
                                <div class="product-meta">
                                    <span class="product-location"><i class="fas fa-map-marker-alt"></i> Dassa</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglet Tubercules -->
                <div class="tab-pane" id="tubercules">
                    <div class="product-grid">
                        <!-- Produit 1 -->
                        <div class="product-card" data-aos="fade-up">
                            <div class="product-image"
                                style="background-image: url('assets/images/produits/patates.jpg');"></div>
                            <div class="product-content">
                                <h3>Tomates Fraîches</h3>
                                <p class="product-description">Tomates rouges juteuses</p>
                                <div class="product-meta">
                                    <span class="product-location"><i class="fas fa-map-marker-alt"></i> Kétou</span>
                                </div>
                            </div>
                        </div>

                        <!-- Produit 2 -->
                        <div class="product-card" data-aos="fade-up" data-aos-delay="100">
                            <div class="product-image"
                                style="background-image: url('assets/images/produits/ignames.jpg');"></div>
                            <div class="product-content">
                                <h3>Piments Locaux</h3>
                                <p class="product-description">Piments forts et aromatiques</p>
                                <div class="product-meta">
                                    <span class="product-location"><i class="fas fa-map-marker-alt"></i> Bohicon</span>
                                </div>
                            </div>
                        </div>

                        <!-- Produit 3 -->
                        <div class="product-card" data-aos="fade-up" data-aos-delay="200">
                            <div class="product-image"
                                style="background-image: url('assets/images/produits/aubergines.png');"></div>
                            <div class="product-content">
                                <h3>Aubergines</h3>
                                <p class="product-description">Aubergines fraîches du jardin</p>
                                <div class="product-meta">
                                    <span class="product-location"><i class="fas fa-map-marker-alt"></i> Dassa</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="features" class="features-section">
        <div class="container">
            <h2 class="section-title">Nos Fonctionnalités Clés</h2>
            <p class="section-subtitle">Découvrez comment AgriConnect révolutionne le marché local</p>

            <div class="features-grid">
                <!-- Carte 1 -->
                <div class="feature-card" data-aos="fade-up">
                    <div class="feature-icon">
                        <i class="fas fa-search-location"></i>
                    </div>
                    <h3>Recherche Intelligente</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Filtrage par produit, région et prix</li>
                        <li><i class="fas fa-check"></i> Carte interactive des producteurs</li>
                        <li><i class="fas fa-check"></i> Suggestions personnalisées</li>
                    </ul>
                    <div class="feature-animation">
                        <div class="smart-search-anim">
                            <div class="anim-container">
                                <!-- Étape 1 : Loupe -->
                                <div class="anim-step search-step">
                                    <i class="fas fa-search fa-2x step-icon"></i>
                                </div>

                                <!-- Étape 2 : Filtres -->
                                <div class="anim-step filter-step">
                                    <div class="filter-tag">
                                        <i class="fas fa-leaf"></i>
                                        <span>Produit</span>
                                    </div>
                                    <div class="filter-tag">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Région</span>
                                    </div>
                                    <div class="filter-tag">
                                        <i class="fas fa-tag"></i>
                                        <span>Prix</span>
                                    </div>
                                </div>

                                <!-- Étape 3 : Carte avec marqueurs -->
                                <div class="anim-step map-step">
                                    <div class="map-container">
                                        <div class="location-pin pin-1"></div>
                                        <div class="location-pin pin-2"></div>
                                        <div class="location-pin pin-3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carte 3 -->
                <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Gestion Simplifiée</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Tableau de bord agriculteur</li>
                        <li><i class="fas fa-check"></i> Systhème de Messagerie intégrée</li>
                    </ul>
                    <div class="feature-animation">
                        <div class="data-visualization">
                            <div class="animated-bars" data-aos="fade-up" id="dynamicDataBars"></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="about" class="about-section">
        <div class="container">
            <div class="about-header">
                <h1>À Propos d'AgriConnect BENIN</h1>
                <div class="header-divider"></div>
                <p class="tagline">La plateforme qui connecte l'agriculture béninoise</p>
            </div>

            <!-- Mission & Vision -->
            <div class="mission-vision-grid">
                <div class="mission-card" data-aos="fade-right">
                    <div class="section-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h2>Notre Rôle</h2>
                    <p><strong>AgriConnect BENIN</strong> est une plateforme digitale innovante conçue pour rapprocher
                        les acteurs de l'agriculture béninoise.</p>
                    <p>Nous offrons aux producteurs un espace de visibilité et aux acheteurs un accès simplifié aux
                        produits locaux, sans intermédiaires superflus.</p>
                </div>

                <div class="vision-card" data-aos="fade-left">
                    <div class="section-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h2>Notre Ambition</h2>
                    <p>Devenir le carrefour numérique incontournable pour une agriculture béninoise connectée, où chaque
                        transaction renforce l'économie locale.</p>
                    <p class="vision-highlight"><strong>Une interface, des opportunités infinies.</strong></p>
                </div>
            </div>

            <!-- Fonctionnalités Plateforme -->
            <div class="platform-features">
                <h2 class="section-title">Comment fonctionne notre plateforme ?</h2>

                <div class="features-grid">
                    <div class="feature-card" data-aos="fade-up">
                        <div class="feature-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h3>Inscription Simple</h3>
                        <p>Créez un profil en quelques minutes, que vous soyez producteur ou acheteur</p>
                    </div>

                    <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="feature-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <h3>Vitrine Digitale</h3>
                        <p>Les producteurs présentent leurs produits avec photos et descriptions détaillées</p>
                    </div>

                    <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="feature-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Recherche Intelligente</h3>
                        <p>Trouvez des produits par localité, catégorie ou disponibilité</p>
                    </div>

                    <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="feature-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3>Messagerie Directe</h3>
                        <p>Échangez en temps réel pour finaliser les transactions</p>
                    </div>
                </div>
            </div>

            <!-- Bénéfices -->
            <div class="benefits-section">
                <h2 class="section-title">Les avantages de notre plateforme</h2>

                <div class="benefits-columns">
                    <div class="benefits-list" data-aos="fade-right">
                        <h3><i class="fas fa-hands-helping"></i> Pour les Producteurs</h3>
                        <ul>
                            <li>Augmentation de votre clientèle potentielle</li>
                            <li>Contrôle total sur vos prix et disponibilités</li>
                            <li>Gain de temps dans la commercialisation</li>
                            <li>Visibilité sur l'ensemble du territoire</li>
                        </ul>
                    </div>

                    <div class="benefits-list" data-aos="fade-left">
                        <h3><i class="fas fa-shopping-basket"></i> Pour les Acheteurs</h3>
                        <ul>
                            <li>Accès à des produits frais et locaux</li>
                            <li>Transparence sur l'origine des produits</li>
                            <li>Possibilité de commander directement à la source</li>
                            <li>Supports de paiement sécurisés</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="about-cta" id="inscription" data-aos="zoom-in">
                <h2>Prêt à transformer votre expérience agricole ?</h2>
                <p>Rejoignez la communauté AgriConnect BENIN et participez à la révolution digitale de l'agriculture
                    béninoise</p>
                <div class="cta-buttons">
                    <a href="identification_agriculteur.php" class="btn btn-primary">Inscription Producteur</a>
                    <a href="identification_marche.php" class="btn btn-secondary">Accéder au Marché</a>
                </div>
            </div>
        </div>
    </section>

    <footer class="agriconnect-footer">
        <div class="footer-container">
            <!-- Section Logo & Description -->
            <div class="footer-section">
                <div class="footer-logo-container">
                    <div class="logo">
                        <img src="assets/images/logo/Logo_back-off.png" alt="Logo AgriConnect BENIN" width="100%">
                    </div>
                </div>
                <p class="footer-description">
                    Plateforme de connexion des acteurs agricoles au Bénin.
                </p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>

            <!-- Section Liens Utiles -->
            <div class="footer-section">
                <h4 class="footer-title">Liens utiles</h4>
                <ul class="footer-links">
                    <li><a href="#">Accueil</a></li>
                    <li><a href="#features">Fonctionnalités</a></li>
                    <li><a href="#about">À propos</a></li>
                </ul>
            </div>

            <!-- Section Contact -->
            <div class="footer-section">
                <h4 class="footer-title">Contactez-nous</h4>
                <ul class="footer-contact">
                    <li><i class="fas fa-map-marker-alt"></i> Cotonou, Bénin</li>
                    <li><i class="fas fa-phone"></i> +229 01 66 49 30 08</li>
                    <li><i class="fas fa-envelope"></i> contact@agriconnectbenin.bj</li>
                </ul>
            </div>

            <!-- Section Newsletter -->
            <div class="footer-section">
                <h4 class="footer-title">Newsletter</h4>
                <p class="footer-newsletter-text">
                    Abonnez-vous pour recevoir nos actualités.
                </p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Votre email" required>
                    <button type="submit">S'abonner</button>
                </form>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p class="copyright">&copy; 2025 AgriConnect Bénin - Tous droits réservés.</p>
            <div class="legal-links">
                <a href="#">Confidentialité</a>
                <a href="#">Conditions d'utilisation</a>
            </div>
        </div>
    </footer>

</body>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));

        btn.classList.add('active');
        document.getElementById(btn.dataset.category).classList.add('active');
    });
});
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser AOS si la bibliothèque est incluse
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    }
});
AOS.init({
    duration: 800,
    once: true
});
</script>
<script>
// Animation Typewriter
const phrases = [
    "Trouver de nouveaux marchés pour vos récoltes",
    "Accède facilement aux produits locaux près de chez vous",
    "Profiter d'une plateforme fluide, intuitive et accessible partout"
];

const textElement = document.getElementById("typewriterText");
let currentPhraseIndex = 0;

function typeWriter() {
    const text = phrases[currentPhraseIndex];
    textElement.textContent = text;
    textElement.style.width = '0';
    textElement.classList.add("typing-effect");

    // Réinitialise l'animation
    void textElement.offsetWidth;

    textElement.style.width = '';

    // Passe à la phrase suivante après animation + pause
    setTimeout(() => {
        textElement.classList.remove("typing-effect");
        currentPhraseIndex = (currentPhraseIndex + 1) % phrases.length;
        setTimeout(typeWriter, 1000); // Pause de 1s
    }, 4000); // Durée de l'animation
}

// Démarrer l'animation
typeWriter();
</script>
<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API = Tawk_API || {},
    Tawk_LoadStart = new Date();
(function() {
    var s1 = document.createElement("script"),
        s0 = document.getElementsByTagName("script")[0];
    s1.async = true;
    s1.src = 'https://embed.tawk.to/683c2d0be4683b190d6133d8/1islgvtul';
    s1.charset = 'UTF-8';
    s1.setAttribute('crossorigin', '*');
    s0.parentNode.insertBefore(s1, s0);
})();
</script>
<!--End of Tawk.to Script-->
<script>
// Fonction pour charger les ressources externes
function loadExternalResources() {
    const resources = [{
            type: 'css',
            url: 'https://unpkg.com/aos@2.3.1/dist/aos.css',
            id: 'aos-css'
        },
        {
            type: 'js',
            url: 'https://unpkg.com/aos@2.3.1/dist/aos.js',
            id: 'aos-js'
        }
    ];

    return Promise.all(resources.map(resource => {
        return new Promise((resolve, reject) => {
            if (document.getElementById(resource.id)) {
                resolve();
                return;
            }

            let element;
            if (resource.type === 'css') {
                element = document.createElement('link');
                element.rel = 'stylesheet';
                element.href = resource.url;
            } else {
                element = document.createElement('script');
                element.src = resource.url;
            }

            element.id = resource.id;
            element.onload = resolve;
            element.onerror = () => {
                console.error(`Échec du chargement de ${resource.url}`);
                reject();
            };

            document.head.appendChild(element);
        });
    }));
}

// Initialisation de l'animation
function initDataVisualization() {
    const container = document.getElementById("dynamicDataBars");
    const barsCount = 4;
    const colors = ['#2F855A'];
    let animationInterval;

    function initVisualization() {
        container.classList.add('loading');

        const bars = Array.from({
            length: barsCount
        }, () => {
            const bar = document.createElement("div");
            bar.className = "data-bar";
            bar.style.height = "0px";
            bar.style.background = colors[0];
            return bar;
        });

        container.append(...bars);

        setTimeout(() => {
            container.classList.remove('loading');
            animateBars();

            animationInterval = setInterval(() => {
                if (Math.random() > 0.7) animateBars(true);
            }, 1000);
        }, 160);
    }

    function animateBars(softUpdate = false) {
        const bars = document.querySelectorAll('.data-bar');
        const maxChange = softUpdate ? 20 : 80;

        bars.forEach((bar, index) => {
            const currentHeight = parseFloat(bar.style.height) || 0;
            const targetHeight = calculateTargetHeight(currentHeight, softUpdate, maxChange);
            const delay = index * 20 + (softUpdate ? 0 : Math.random() * 60);

            setTimeout(() => {
                animateBar(bar, targetHeight);
            }, delay);
        });
    }

    function calculateTargetHeight(currentHeight, softUpdate, maxChange) {
        if (softUpdate) {
            const variation = Math.random() * maxChange;
            return Math.max(10, Math.min(100, currentHeight + (Math.random() > 0.5 ? variation : -variation)));
        }
        return 30 + Math.random() * 70;
    }

    function animateBar(bar, targetHeight) {
        bar.style.height = `${targetHeight}%`;
        bar.dataset.value = `${Math.round(targetHeight)}%`;
        bar.classList.add('active');

        bar.style.transform = 'scaleY(1.1)';
        setTimeout(() => {
            bar.style.transform = 'scaleY(1)';
        }, 60);
    }

    window.addEventListener('beforeunload', () => {
        clearInterval(animationInterval);
    });

    initVisualization();
}

// Exécution au chargement de la page
document.addEventListener("DOMContentLoaded", () => {
    loadExternalResources()
        .then(() => {
            AOS.init({
                once: true,
                easing: 'ease-out-quad',
                duration: 800
            });
            initDataVisualization();
        })
        .catch(error => {
            console.error("Erreur lors du chargement des ressources:", error);
            initDataVisualization();
        });
});
// Fonctions pour gérer la modal
function openLoginModal() {
    document.getElementById('loginModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeLoginModal() {
    document.getElementById('loginModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Fermer la modal en cliquant à l'extérieur
window.onclick = function(event) {
    const modal = document.getElementById('loginModal');
    if (event.target == modal) {
        closeLoginModal();
    }
}

// Fermer avec la touche ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeLoginModal();
    }
});
</script>

</html>