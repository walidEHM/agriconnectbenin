<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Vérification de code</title>
    <style>
    .logo {
        background: none;
    }

    .code-copied {
        background-color: #E6FFFA;
        border-color: #4FD1C5 !important;
        transition: all 0.3s ease;
    }

    body {
        background: #f5f5f5;
        color: #2D3748;
        line-height: 1.5;
        top: 0;
        bottom: 0;
    }

    .container {
        max-width: 500px;
        margin: 40px auto;
        padding: 0 20px;
    }

    /* En-tête */
    .logo-container {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
        justify-content: center;
    }

    .logo-icon {
        width: 50px;
        height: 50px;
    }

    .logo-text {
        font-size: 22px;
        font-weight: 700;
        color: #2F855A;
    }

    .logo-text span {
        color: #ED8936;
    }

    /* Carte principale */
    .card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    h1 {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        color: #2F855A;
        font-size: 24px;
        margin-bottom: 20px;
        text-align: center;
    }

    /* Formulaire */
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #2D3748;
    }

    .form-input {
        width: 100%;
        padding: 12px;
        border: 1px solid #A0AEC0;
        border-radius: 8px;
        font-family: 'Inter', sans-serif;
        font-size: 16px;
    }

    .form-input:focus {
        outline: none;
        border-color: #2F855A;
    }

    /* Bouton */
    .button {
        display: inline-block;
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        font-weight: 500;
        text-align: center;
        cursor: pointer;
        transition: background-color 0.3s;
        border: none;
        font-size: 16px;
        font-family: 'Inter', sans-serif;
    }

    .button-primary {
        background-color: #2F855A;
        color: white;
    }

    .button-primary:hover {
        background-color: #276749;
    }

    /* Notifications */
    .notification {
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .notification-success {
        background-color: #C6F6D5;
        color: #22543D;
    }

    .notification-error {
        background-color: #FED7D7;
        color: #9B2C2C;
    }

    /* Lien */
    .text-center {
        text-align: center;
    }

    .link {
        color: #2F855A;
        text-decoration: none;
        font-weight: 500;
    }

    .link:hover {
        text-decoration: underline;
    }

    /* Code à 6 chiffres */
    .code-inputs {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .code-input {
        width: 45px;
        height: 60px;
        text-align: center;
        font-size: 24px;
        border: 1px solid #A0AEC0;
        border-radius: 8px;
        font-family: 'Inter', sans-serif;
    }

    .code-input:focus {
        outline: none;
        border-color: #2F855A;
    }

    @media (min-width: 768px) {
        body {
            padding: 0 64px;
        }
    }

    .spinner {
        display: none;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
    </style>
</head>
<?php
require_once __DIR__.'/data/dbconn.php';


// Redirect if no email provided
if(!isset($_GET['email'])) {
    header('location: identification_marche.php#showLoginForm');
    exit();
}

// Validate email format
$email = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);
if(!$email) {
    header('location: identification_marche.php#showLoginForm');
    exit();
}

$email = htmlspecialchars($email);

// Check if token exists for this email
$sql = 'SELECT token_verification, expiration_token FROM marches 
        WHERE email = ? AND compte_active = 1 AND token_verification IS NOT NULL';
$stmt = $conn->prepare($sql);
$stmt->execute([$email]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$result) {
    // No record found for this email
    header('location: identification_marche.php#showLoginForm');
    exit();
}


// Vérification de la date d'expiration
$now = new DateTime();
$expiration = new DateTime($result['expiration_token']);

if($now > $expiration) {
    // Le token a expiré
    echo '<div class="container">
        <div class="logo-container">
            <div class="logo">
                <img src="assets/images/logo/Logo_back-off.png" alt="Logo AgriConnect BENIN" height="70px">
            </div>
        </div>
        <div class="card">
            <div class="notification notification-error">
                Le code de vérification a expiré. Veuillez 
                <a href="data/renvoyer_code_marche.php?email='.urlencode(htmlspecialchars($email)).'" class="link">demander un nouveau code</a>.
            </div>
        </div>
        <div class="text-center">
            <a href="identification_marche.php#showLoginForm" class="link">Retour à la page de connexion</a>
        </div>
    </div>';
exit();
}
?>
<?php

// Gestion des messages de retour
$success = $_GET['success'] ?? false;
$error = $_GET['error'] ?? false;

if ($success): ?>
<div class="notification notification-success">
    Un nouveau code a été envoyé à votre adresse email.
</div>
<?php endif;

if ($error): ?>
<div class="notification notification-error">
    <?php
        switch ($error) {
            case 'email_invalide':
                echo "L'adresse email est invalide.";
                break;
            case 'compte_non_actif':
                echo "Votre compte n'est pas encore activé.";
                break;
            case 'erreur_envoi':
                echo "Erreur lors de l'envoi du code. Veuillez réessayer.";
                break;
            case 'code_incorrect':
                echo "Code de validation incorrect";
                break;
            case 'code_expire':
                echo "Code de validation expiré, veuillez demandez un nouveau code";
                break;
            case 'token_expired':
                echo "Le lien de reinitialisation a exoiré; nous avons envoyer un nouveau a votre adresse email";
                break;
            default:
                echo "Une erreur est survenue. Veuillez réessayer.";
        }
        ?>
</div>
<?php endif; ?>

<body>
    <div class="container">
        <div class="logo-container">
            <div class="logo">
                <img src="assets/images/logo/Logo_back-off.png" alt="Logo AgriConnect BENIN" height="70px">
            </div>
        </div>

        <div class="card">
            <h1>Vérification du code de sécurité</h1>

            <p style="margin-bottom: 20px; text-align: center;">Nous avons envoyé un code à 6 chiffres à l'adresse
                <strong><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></strong>. Veuillez le saisir
                ci-dessous :
            </p>

            <form action="data/traitement_verification_code_marche.php" method="POST">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="code-inputs">
                    <input type="text" name="code1" class="code-input" maxlength="1" pattern="[0-9]" required autofocus>
                    <input type="text" name="code2" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" name="code3" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" name="code4" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" name="code5" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" name="code6" class="code-input" maxlength="1" pattern="[0-9]" required>
                </div>
                <button type="submit" class="button button-primary" id="submitButton">
                    <span id="buttonText">Vérifier le code</span>
                    <span id="buttonSpinner" class="fa fa-spinner fa-spin"
                        style="display:none; margin-left: 5px;"></span>
                </button>
            </form>

            <div style="margin-top: 20px; text-align: center;">
                <p>Vous n'avez pas reçu de code ?
                    <a href="data/renvoyer_code_marche.php?email=<?php echo urlencode($email); ?>"
                        class="link">Renvoyer
                        le code</a>
                </p>
            </div>
        </div>

        <div class="text-center">
            <a href="identification_marche.php#showLoginForm" class="link">Retour à la page de connexion</a>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.code-input');
        const form = document.querySelector('form');
        const submitButton = document.getElementById('submitButton');
        const buttonText = document.getElementById('buttonText');
        const buttonSpinner = document.getElementById('buttonSpinner');
        const resendLink = document.querySelector('a[href*="renvoyer_code_marche.php"]');

        // Initialisation des champs
        function initInputs() {
            inputs.forEach((input, index) => {
                // Configuration pour mobile et accessibilité
                input.setAttribute('inputmode', 'numeric');
                input.setAttribute('pattern', '[0-9]*');
                input.setAttribute('aria-label', `Chiffre ${index + 1} du code`);
                input.setAttribute('maxlength', '1');
                input.value = '';

                // Empêcher les caractères non-numériques
                input.addEventListener('input', handleInput);

                // Gestion du collage
                input.addEventListener('paste', handlePaste);

                // Navigation au clavier
                input.addEventListener('keydown', handleKeyDown);
            });

            // Focus initial sur le premier champ
            inputs[0].focus();
        }

        // Gestion de la saisie
        function handleInput(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
            if (e.target.value.length > 0 && e.target.nextElementSibling && e.target.nextElementSibling
                .classList.contains('code-input')) {
                e.target.nextElementSibling.focus();
            }
        }

        // Gestion du collage
        function handlePaste(e) {
            e.preventDefault();
            const pasteData = e.clipboardData.getData('text').trim();

            if (pasteData.match(/^\d{6}$/)) {
                pasteData.split('').forEach((char, i) => {
                    if (inputs[i]) {
                        inputs[i].value = char;
                        inputs[i].classList.add('code-copied');
                    }
                });

                setTimeout(() => {
                    inputs.forEach(inp => inp.classList.remove('code-copied'));
                }, 300);

                inputs[5].focus();
            } else {
                showError("Le code collé doit contenir exactement 6 chiffres");
                flashError(inputs);
            }
        }

        // Gestion des touches du clavier
        function handleKeyDown(e) {
            const currentIndex = Array.from(inputs).indexOf(e.target);

            switch (e.key) {
                case 'Backspace':
                    if (!e.target.value && currentIndex > 0) {
                        inputs[currentIndex - 1].focus();
                    }
                    break;
                case 'ArrowLeft':
                    if (currentIndex > 0) {
                        inputs[currentIndex - 1].focus();
                    }
                    break;
                case 'ArrowRight':
                    if (currentIndex < inputs.length - 1) {
                        inputs[currentIndex + 1].focus();
                    }
                    break;
                case 'Enter':
                    if (currentIndex === inputs.length - 1) {
                        submitForm();
                    }
                    break;
                default:
                    // Auto-avance si un chiffre est saisi
                    if (e.target.value && e.key !== 'Backspace' && currentIndex < inputs.length - 1) {
                        inputs[currentIndex + 1].focus();
                    }
            }
        }

        // Affichage des erreurs
        function showError(message) {
            const errorElement = document.createElement('div');
            errorElement.className = 'notification notification-error';
            errorElement.textContent = message;
            errorElement.setAttribute('role', 'alert');

            const card = document.querySelector('.card');
            card.insertBefore(errorElement, form);

            setTimeout(() => {
                errorElement.remove();
            }, 5000);
        }

        // Animation d'erreur sur les champs
        function flashError(inputs) {
            inputs.forEach(input => {
                input.classList.add('error');
                setTimeout(() => input.classList.remove('error'), 1000);
            });
        }

        // Soumission du formulaire
        function submitForm() {
            // Vérifier que tous les champs sont remplis
            const allFilled = Array.from(inputs).every(input => input.value);

            if (!allFilled) {
                showError("Veuillez remplir tous les champs du code");
                flashError(inputs);
                return;
            }

            // Afficher le spinner et désactiver le bouton
            buttonText.style.display = 'none';
            buttonSpinner.style.display = 'inline-block';
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');

            // Soumission réelle
            form.submit();
        }

        // Gestion du lien de renvoi
        if (resendLink) {
            resendLink.addEventListener('click', function(e) {
                if (submitButton.disabled) {
                    e.preventDefault();
                    showError("Veuillez patienter pendant la vérification");
                }
            });
        }

        // Initialisation
        initInputs();
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm();
        });
    });
    </script>
</body>

</html>