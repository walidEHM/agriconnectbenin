<?php
session_start();
if(isset($_SESSION['success_password_change'])){
    echo "<script>alert('" . $_SESSION['success_password_change'] . "');</script>";
    unset($_SESSION['success_password_change']);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriConnect BENIN - Plateforme Agricole</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        color: black;
        background-color: #F8FAFC;
        display: flex;
        min-height: 100vh;
        line-height: 1.6;
    }

    /* Section gauche - Hero */
    .left-section {
        width: 50%;
        min-height: 100vh;
        background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url("assets/images/banniere/barner.png");
        background-size: cover;
        background-position: center;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 4rem;
        color: #FFFFFF;
    }

    .left-content {
        max-width: 500px;
        margin: 0 auto;
        position: relative;
        z-index: 10;
    }

    .logo-container {
        margin-bottom: 2.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        background-color: #F0FFF4;
        padding: 15px;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        width: 50%;
        justify-content: center;
    }

    .logo-container img {
        height: 60px;
        width: auto;
        border-radius: 8px;
    }

    .logo-text {
        font-size: 1.5rem;
        font-weight: 700;
        color: #FFFFFF;
    }

    .left-section h2 {
        font-size: 2.25rem;
        margin-bottom: 1.5rem;
        font-weight: 700;
        line-height: 1.3;
    }

    .left-section p {
        margin-bottom: 2rem;
        font-size: 1.1rem;
        opacity: 0.9;
    }

    .features {
        margin: 2rem 0;
    }

    .feature-item {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }

    .feature-icon {
        background-color: rgba(56, 161, 105, 0.2);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        color: #38A169;
    }

    .left-section a {
        color: #FFFFFF;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .left-section a:hover {
        color: #38A169;
        text-decoration: underline;
    }

    .left-section a i {
        margin-left: 0.5rem;
        transition: transform 0.3s ease;
    }

    .left-section a:hover i {
        transform: translateX(3px);
    }

    /* Section droite - Formulaire */
    .right-section {
        width: 50%;
        min-height: 100vh;
        padding: 2rem 4rem;
        background-color: #FFFFFF;
    }

    .form-container {
        max-width: 500px;
        margin: 0 auto;
        padding: 2rem 0;
    }

    .form-header {
        margin-bottom: 2.5rem;
        text-align: center;
    }

    .form-header h2 {
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
        color: #2F855A;
        font-weight: 700;
    }

    .form-header p {
        color: black;
        font-size: 0.95rem;
    }

    /* Boutons */
    .button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.875rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        width: 100%;
        margin-bottom: 1rem;
        font-size: 1rem;
    }

    .google-btn {
        background-color: #FFFFFF;
        color: black;
        border: 1px solid #E2E8F0;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .google-btn:hover {
        background-color: #E2E8F0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .google-icon {
        width: 20px;
        height: 20px;
        margin-right: 10px;
    }

    /* Divider */
    .divider {
        display: flex;
        align-items: center;
        margin: 1.5rem 0;
        color: #718096;
        font-size: 0.875rem;
    }

    .divider::before,
    .divider::after {
        content: "";
        flex: 1;
        border-bottom: 1px solid #E2E8F0;
    }

    .divider-text {
        padding: 0 1rem;
    }

    /* Form elements */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: black;
        font-size: 0.95rem;
    }

    .input-container {
        position: relative;
    }

    .form-input {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background-color: #FFFFFF;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .form-input:focus {
        outline: none;
        border-color: #2F855A;
        box-shadow: 0 0 0 3px rgba(47, 133, 90, 0.2);
    }

    .input-icon {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #718096;
        cursor: pointer;
    }

    .password-toggle:hover {
        color: #2F855A;
    }

    /* Primary button */
    .button-primary {
        background-color: #2F855A;
        color: #FFFFFF;
        font-weight: 600;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .button-primary:hover {
        background-color: #276749;
        transform: translateY(-1px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .button-primary i {
        margin-right: 0.5rem;
    }

    /* Links */
    .form-footer {
        text-align: center;
        margin-top: 2rem;
        color: #718096;
        font-size: 0.95rem;
    }

    .form-footer a {
        color: #2F855A;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
    }

    .form-footer a:hover {
        text-decoration: underline;
    }

    /* Toggle forms */
    .login-form {
        display: none;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.5s ease;
    }

    .login-form.active {
        display: block;
        opacity: 1;
        transform: translateY(0);
    }

    .register-form {
        display: block;
        opacity: 1;
        transform: translateY(0);
        transition: all 0.5s ease;
    }

    .register-form.hidden {
        display: none;
        opacity: 0;
        transform: translateY(-20px);
    }

    /* Error messages */
    .error-message {
        color: #E53E3E;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: none;
    }

    .error-border {
        border-color: #E53E3E !important;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .left-section {
            padding: 2rem;
        }

        .right-section {
            padding: 2rem;
        }
    }

    @media (max-width: 768px) {
        body {
            flex-direction: column;
            height: auto;
        }

        .left-section {
            width: 100%;
            min-height: auto;
            padding: 3rem 1.5rem;
        }

        .right-section {
            width: 100%;
            min-height: auto;
            padding: 3rem 1.5rem;
            overflow-y: visible;
        }

        .left-content,
        .form-container {
            max-width: 100%;
        }
    }

    @media (max-width: 480px) {

        .left-section,
        .right-section {
            padding: 2rem 1rem;
        }

        .left-section h2 {
            font-size: 1.75rem;
        }

        .form-header h2 {
            font-size: 1.5rem;
        }

        .button {
            padding: 0.75rem 1rem;
        }
    }
    </style>
</head>

<body>
    <div class="left-section">
        <div class="left-content" id="leftContent">
            <div class="logo-container">
                <img src="assets/images/logo/Logo_back-off.png" alt="Logo AgriConnect BENIN">
            </div>
            <h2 id="leftTitle">Rejoignez la révolution agricole au Bénin</h2>
            <p id="leftText">Connectez-vous directement avec des acheteurs locaux et internationaux, gérez votre
                production et développez votre activité grâce à notre plateforme dédiée aux professionnels de
                l'agriculture.</p>

            <div class="features">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span>Suivi analytique de votre production</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <span>Mise en relation avec des acheteurs certifiés</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-tractor"></i>
                    </div>
                    <span>Outils modernes de gestion agricole</span>
                </div>
            </div>

            <p id="leftLink">Déjà membre? <a id="toggleFormLink" href="#">Connectez-vous ici <i
                        class="fas fa-arrow-right"></i></a></p>
        </div>
    </div>

    <div class="right-section">
        <div class="form-container">
            <!-- Formulaire d'inscription -->
            <div class="register-form" id="registerForm">
                <div class="form-header">
                    <h2>Créer un compte Agriculteur</h2>
                    <p>Rejoignez notre communauté et accédez à tous nos services</p>
                </div>

                <form id="registrationForm" action="data/traitement_inscription_agriculteur.php" method="post"
                    enctype="multipart/form-data" onsubmit="return validateForm()">
                    <div class="form-group">
                        <label for="nom" class="form-label">Nom complet</label>
                        <div style="display: flex; gap: 1rem;">
                            <div style="flex: 1;">
                                <input type="text" id="prenom" name="prenom" class="form-input" placeholder="Prénom"
                                    required>
                                <div id="prenom-error" class="error-message">Veuillez entrer votre prénom</div>
                            </div>
                            <div style="flex: 1;">
                                <input type="text" id="nom" name="nom" class="form-input" placeholder="Nom" required>
                                <div id="nom-error" class="error-message">Veuillez entrer votre nom</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Adresse email</label>
                        <div class="input-container">
                            <input type="email" id="email" name="email" class="form-input"
                                placeholder="exemple@email.com" required>
                            <i class="fas fa-envelope input-icon"></i>
                        </div>
                        <div id="email-error" class="error-message">Veuillez entrer une adresse email valide</div>
                    </div>

                    <div class="form-group">
                        <label for="mot_de_passe" class="form-label">Mot de passe</label>
                        <div class="input-container">
                            <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-input"
                                placeholder="••••••••" required>
                            <i class="fas fa-eye password-toggle input-icon" id="togglePassword"></i>
                        </div>
                        <div id="password-error" class="error-message">Le mot de passe doit contenir au moins 8
                            caractères</div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirmation du mot de passe</label>
                        <div class="input-container">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input"
                                placeholder="••••••••" required>
                            <i class="fas fa-eye password-toggle input-icon" id="toggleConfirmPassword"></i>
                        </div>
                        <div id="confirm-error" class="error-message">Les mots de passe ne correspondent pas</div>
                    </div>

                    <button type="submit" class="button button-primary">
                        <i class="fas fa-user-plus"></i> Créer mon compte
                    </button>
                </form>

                <div class="form-footer">
                    Déjà inscrit? <a id="showLoginForm" href="#">Connectez-vous</a>
                </div>
            </div>

            <!-- Formulaire de connexion -->
            <div class="login-form" id="loginForm">
                <div class="form-header">
                    <h2>Connectez-vous</h2>
                    <p>Accédez à votre tableau de bord personnel</p>
                </div>

                <form action="data/traitement_connexion_agriculteur.php" method="post">
                    <div class="form-group">
                        <label for="login_email" class="form-label">Adresse email</label>
                        <div class="input-container">
                            <input type="email" id="login_email" name="email" class="form-input"
                                placeholder="exemple@email.com" required>
                            <i class="fas fa-envelope input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="login_password" class="form-label">Mot de passe</label>
                        <div class="input-container">
                            <input type="password" id="login_password" name="mot_de_passe" class="form-input"
                                placeholder="••••••••" required>
                            <i class="fas fa-eye password-toggle input-icon" id="toggleLoginPassword"></i>
                        </div>
                    </div>

                    <!-- Bouton dans le formulaire de connexion -->
                    <div class="form-group" style="text-align: right;">
                        <a href="#" id="lien_mot_de_passe_oublie_agriculteur"
                            style="font-size: 0.875rem; color: #2F855A;">Mot de passe oublié?</a>
                    </div>

                    <button type="submit" class="button button-primary">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                </form>

                <div class="form-footer">
                    Pas encore de compte? <a id="showRegisterForm" href="#">Créer un compte</a>
                </div>
            </div>
        </div>
    </div>


    <!-- Modale Mot de passe oublié -->

    <div class="modale_mot_de_passe_oublie_agriculteur" id="modale_mot_de_passe_oublie_agriculteur">
        <div class="conteneur_modale_mot_de_passe_oublie_agriculteur">
            <div class="entete_modale_mot_de_passe_oublie_agriculteur">
                <h2>Réinitialiser votre mot de passe</h2>
                <button class="bouton_fermeture_modale_mot_de_passe_oublie_agriculteur"
                    id="bouton_fermeture_modale_mot_de_passe_oublie_agriculteur">&times;</button>
            </div>

            <div class="corps_modale_mot_de_passe_oublie_agriculteur">
                <p class="description_modale_mot_de_passe_oublie_agriculteur">Entrez votre adresse email
                    pour recevoir un lien de réinitialisation</p>

                <form id="formulaire_mot_de_passe_oublie_agriculteur" method="post"
                    action="data/traitement_mot_de_passe_oublie_agriculteur.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="email_mot_de_passe_oublie_agriculteur" class="form-label">Adresse
                            email</label>
                        <div class="input-container">
                            <input type="email" id="email_mot_de_passe_oublie_agriculteur" name="email"
                                class="form-input" placeholder="exemple@email.com" required>
                            <i class="fas fa-envelope input-icon"></i>
                        </div>
                        <div id="erreur_email_mot_de_passe_oublie_agriculteur"
                            class="message_erreur_mot_de_passe_oublie_agriculteur">Veuillez entrer une
                            adresse email valide</div>
                    </div>

                    <button type="submit" class="button button-primary">
                        <i class="fas fa-paper-plane"></i> Envoyer la demande
                    </button>
                </form>
            </div>

            <div class="pied_modale_mot_de_passe_oublie_agriculteur">
                <a href="#" id="lien_retour_connexion_mot_de_passe_oublie_agriculteur">Retour à la
                    connexion</a>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    // CONSTANTES ET VARIABLES
    const DOM = {
        // Formulaires
        registerForm: document.getElementById('registerForm'),
        loginForm: document.getElementById('loginForm'),
        registrationForm: document.getElementById('registrationForm'),

        // Boutons de bascule
        showLoginForm: document.getElementById('showLoginForm'),
        showRegisterForm: document.getElementById('showRegisterForm'),
        toggleFormLink: document.getElementById('toggleFormLink'),

        // Champs de formulaire
        nomagriculteur: document.getElementById('nom_agriculteur'),
        email: document.getElementById('email'),
        password: document.getElementById('mot_de_passe'),
        confirmPassword: document.getElementById('confirm_password'),
        loginPassword: document.getElementById('login_password'),
        terms: document.getElementById('terms'),

        // Messages d'erreur
        nomagriculteurError: document.getElementById('nom_agriculteur-error'),
        emailError: document.getElementById('email-error'),
        passwordError: document.getElementById('password-error'),
        confirmError: document.getElementById('confirm-error'),

        // Contenu dynamique
        leftTitle: document.getElementById('leftTitle'),
        leftText: document.getElementById('leftText'),
        leftLink: document.getElementById('leftLink'),

        // Modale mot de passe oublié
        modaleMotDePasseOublie: document.getElementById('modale_mot_de_passe_oublie_agriculteur'),
        lienMotDePasseOublie: document.getElementById('lien_mot_de_passe_oublie_agriculteur'),
        boutonFermetureModale: document.getElementById('bouton_fermeture_modale_mot_de_passe_oublie_agriculteur'),
        formulaireMotDePasseOublie: document.getElementById('formulaire_mot_de_passe_oublie_agriculteur')
    };

    // FONCTIONS UTILITAIRES
    const utils = {
        // Validation email
        validateEmail: (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email),

        // Afficher/masquer erreur
        toggleError: (element, show, errorElement = null) => {
            if (!element) return;

            if (show) {
                element.classList.add('error-border');
                if (errorElement) {
                    errorElement.style.display = 'block';
                }
            } else {
                element.classList.remove('error-border');
                if (errorElement) {
                    errorElement.style.display = 'none';
                }
            }
        },

        // Toggle password visibility
        togglePasswordVisibility: (input, toggleIcon) => {
            if (!input || !toggleIcon) return;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            toggleIcon.classList.toggle('fa-eye-slash');
        }
    };

    // GESTION DES FORMULAIRES
    const formManager = {
        init: function() {
            // Basculer entre login/register
            if (DOM.showLoginForm) {
                DOM.showLoginForm.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.showLogin();
                });
            }

            if (DOM.showRegisterForm) {
                DOM.showRegisterForm.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.showRegister();
                });
            }

            if (DOM.toggleFormLink) {
                DOM.toggleFormLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.showLogin();
                });
            }

            // Validation en temps réel
            this.setupValidation();
        },

        showLogin: function() {
            if (DOM.registerForm) DOM.registerForm.classList.add('hidden');
            if (DOM.loginForm) {
                setTimeout(() => {
                    DOM.loginForm.classList.add('active');
                }, 50);
            }

            // Mise à jour du contenu
            if (DOM.leftTitle) DOM.leftTitle.textContent = "Content de vous revoir!";
            if (DOM.leftText) DOM.leftText.textContent = "Accédez à votre espace marché et découvrez de nouveaux producteurs...";
            if (DOM.leftLink) DOM.leftLink.innerHTML = 'Pas encore de compte? <a id="toggleFormLink" href="#">Inscrivez-vous ici <i class="fas fa-arrow-right"></i></a>';

            // Réattacher les événements
            const newToggleLink = document.getElementById('toggleFormLink');
            if (newToggleLink) {
                newToggleLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.showRegister();
                });
            }
        },

        showRegister: function() {
            if (DOM.loginForm) DOM.loginForm.classList.remove('active');
            if (DOM.registerForm) {
                setTimeout(() => {
                    DOM.registerForm.classList.remove('hidden');
                }, 50);
            }

            if (DOM.leftTitle) DOM.leftTitle.textContent = "Connectez-vous aux meilleurs producteurs du Bénin";
            if (DOM.leftText) DOM.leftText.textContent = "Accédez à un réseau d'agriculteurs locaux de confiance...";
            if (DOM.leftLink) DOM.leftLink.innerHTML = 'Déjà membre? <a id="toggleFormLink" href="#">Connectez-vous ici <i class="fas fa-arrow-right"></i></a>';

            const newToggleLink = document.getElementById('toggleFormLink');
            if (newToggleLink) {
                newToggleLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.showLogin();
                });
            }
        },

        setupValidation: function() {
            // Validation inscription
            if (DOM.registrationForm) {
                DOM.registrationForm.addEventListener('submit', (e) => {
                    if (!this.validateRegistrationForm()) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    return true;
                });

                // Validation en temps réel
                if (DOM.nomagriculteur) {
                    DOM.nomagriculteur.addEventListener('input', () => {
                        utils.toggleError(DOM.nomagriculteur, false, DOM.nomagriculteurError);
                    });
                }

                if (DOM.email) {
                    DOM.email.addEventListener('input', () => {
                        utils.toggleError(DOM.email, false, DOM.emailError);
                    });
                }

                if (DOM.password) {
                    DOM.password.addEventListener('input', () => {
                        utils.toggleError(DOM.password, false, DOM.passwordError);
                    });
                }

                if (DOM.confirmPassword) {
                    DOM.confirmPassword.addEventListener('input', () => {
                        utils.toggleError(DOM.confirmPassword, false, DOM.confirmError);
                    });
                }
            }
        },

        validateRegistrationForm: function() {
            let isValid = true;

            // Validation nom marché
            if (DOM.nomagriculteur && DOM.nomagriculteur.value.trim() === '') {
                utils.toggleError(DOM.nomagriculteur, true, DOM.nomagriculteurError);
                isValid = false;
            }

            // Validation email
            if (DOM.email && !utils.validateEmail(DOM.email.value)) {
                utils.toggleError(DOM.email, true, DOM.emailError);
                isValid = false;
            }

            // Validation mot de passe
            if (DOM.password && DOM.password.value.length < 8) {
                utils.toggleError(DOM.password, true, DOM.passwordError);
                isValid = false;
            }

            // Validation confirmation mot de passe
            if (DOM.password && DOM.confirmPassword && DOM.password.value !== DOM.confirmPassword.value) {
                utils.toggleError(DOM.confirmPassword, true, DOM.confirmError);
                isValid = false;
            }

            // Validation checkbox conditions
            if (DOM.terms && !DOM.terms.checked) {
                alert('Veuillez accepter les conditions d\'utilisation');
                isValid = false;
            }

            return isValid;
        }
    };

    // GESTION DES MOTS DE PASSE
    const passwordManager = {
        init: function() {
            // Toggle visibilité
            const togglePassword = document.querySelector('#togglePassword');
            if (togglePassword && DOM.password) {
                togglePassword.addEventListener('click', () => {
                    utils.togglePasswordVisibility(DOM.password, togglePassword);
                });
            }

            const toggleLoginPassword = document.querySelector('#toggleLoginPassword');
            if (toggleLoginPassword && DOM.loginPassword) {
                toggleLoginPassword.addEventListener('click', () => {
                    utils.togglePasswordVisibility(DOM.loginPassword, toggleLoginPassword);
                });
            }

            const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
            if (toggleConfirmPassword && DOM.confirmPassword) {
                toggleConfirmPassword.addEventListener('click', () => {
                    utils.togglePasswordVisibility(DOM.confirmPassword, toggleConfirmPassword);
                });
            }
        }
    };

    // GESTION DE LA MODALE
    const modalManager = {
        init: function() {
            if (!DOM.modaleMotDePasseOublie) return;

            // Événements
            if (DOM.lienMotDePasseOublie) {
                DOM.lienMotDePasseOublie.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.openModal();
                });
            }

            if (DOM.boutonFermetureModale) {
                DOM.boutonFermetureModale.addEventListener('click', this.closeModal);
            }

            if (DOM.modaleMotDePasseOublie) {
                DOM.modaleMotDePasseOublie.addEventListener('click', (e) => {
                    if (e.target === DOM.modaleMotDePasseOublie) this.closeModal();
                });
            }

            // Soumission formulaire
            if (DOM.formulaireMotDePasseOublie) {
                DOM.formulaireMotDePasseOublie.addEventListener('submit', (e) => {
                    this.handlePasswordReset(e);
                });
            }
        },

        openModal: function() {
            DOM.modaleMotDePasseOublie.style.display = 'flex';
            setTimeout(() => {
                DOM.modaleMotDePasseOublie.classList.add('active');
                const emailInput = document.getElementById('email_mot_de_passe_oublie_agriculteur');
                if (emailInput) emailInput.focus();
            }, 10);
        },

        closeModal: function() {
            DOM.modaleMotDePasseOublie.classList.remove('active');
            setTimeout(() => {
                DOM.modaleMotDePasseOublie.style.display = 'none';
            }, 300);
        },

        handlePasswordReset: async function(e) {
            e.preventDefault();
            const form = e.target;
            const emailInput = form.querySelector('input[type="email"]');
            const submitBtn = form.querySelector('[type="submit"]');
            const errorElement = document.getElementById('erreur_email_mot_de_passe_oublie_agriculteur');

            // Réinitialisation UI
            if (emailInput) emailInput.classList.remove('error-border');
            if (errorElement) errorElement.style.display = 'none';

            // Validation
            if (!emailInput || !utils.validateEmail(emailInput.value)) {
                if (emailInput) emailInput.classList.add('error-border');
                if (errorElement) {
                    errorElement.textContent = 'Veuillez entrer une adresse email valide';
                    errorElement.style.display = 'block';
                }
                return;
            }

            // Soumission
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Demande en cours...
                `;
            }

            try {
                const formData = new FormData();
                formData.append('email', emailInput.value);

                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error('Erreur réseau');

                const data = await response.json();
                console.log("Succès:", data);

                // Affiche dynamiquement le message renvoyé par le serveur
                alert(data.message);

                if (data.success) {
                    setTimeout(() => {
                        window.location.href = 'verifier_code_de_mot_de_passe_oublie_agriculteur.php?email=' + 
                            encodeURIComponent(data.email);
                    }, 2000);
                }

            } catch (error) {
                console.error("Erreur:", error);
                if (errorElement) {
                    errorElement.textContent = "Une erreur est survenue, veuillez réessayer";
                    errorElement.style.display = 'block';
                }
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer la demande';
                }
            }
        }
    };

    // INITIALISATION
    formManager.init();
    passwordManager.init();
    modalManager.init();

    // Vérifier hash URL
    if (window.location.hash === '#showLoginForm') {
        formManager.showLogin();
    }
});
    </script>
</body>

</html>