<?php
session_start();

// Vérification de session plus robuste
if (!isset($_SESSION['authemail']) || $_SESSION['authemail'] !== true) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
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

<body>
    <div class="container">
        <div class="card">
            <div class="logo-container">
                <img src="../../assets/images/logo/Logo_back-off.png" alt="Logo AgriConnect BENIN" height="55">
            </div>
            <h1>Vérification du code administrateur</h1>

            <?php if (isset($error)): ?>
            <div class="notification notification-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <p style="text-align: center; margin-bottom: 15px;">
                Veuillez saisir votre: <strong>code de confirmation</strong>
            </p>

            <form id="codeForm" method="POST" action="data/verification_code.php">
                <div class="code-inputs">
                    <input type="text" name="code1" class="code-input" maxlength="1" pattern="[0-9]" required autofocus>
                    <input type="text" name="code2" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" name="code3" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" name="code4" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" name="code5" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" name="code6" class="code-input" maxlength="1" pattern="[0-9]" required>
                </div>

                <button type="submit" class="button button-primary" id="submitBtn">
                    Vérifier le code
                </button>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <a href="data/traitement_authemail.php" class="link">Renvoyer le code</a>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('codeForm');
        const submitBtn = document.getElementById('submitBtn');
        const inputs = document.querySelectorAll('.code-input');

        // Auto-focus et navigation entre les champs
        inputs.forEach((input, index) => {
            // Gestion de la saisie
            input.addEventListener('input', function() {
                // N'autoriser que les chiffres
                this.value = this.value.replace(/\D/g, '');

                // Passer au champ suivant si un chiffre est saisi
                if (this.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }

                // Si tous les champs sont remplis, soumettre le formulaire
                if (index === inputs.length - 1 && this.value.length === 1) {
                    const allFilled = Array.from(inputs).every(i => i.value.length === 1);
                    if (allFilled) {
                        submitBtn.focus();
                    }
                }
            });

            // Gestion du backspace
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '' && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            // Collage du code depuis le clipboard
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text/plain').trim();

                // Vérifier que c'est un code à 6 chiffres
                if (/^\d{6}$/.test(pasteData)) {
                    for (let i = 0; i < 6; i++) {
                        if (inputs[i]) {
                            inputs[i].value = pasteData[i];
                        }
                    }
                    // Focus sur le dernier champ
                    if (inputs[5]) {
                        inputs[5].focus();
                    }
                }
            });
        });

        // Soumission du formulaire
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Désactiver le bouton et afficher le spinner
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Vérification en cours...';

            // Simuler un délai pour la vérification (à remplacer par une requête AJAX réelle)
            setTimeout(() => {
                // Soumettre le formulaire
                form.submit();
            }, 1000);
        });

        // Focus automatique sur le premier champ vide
        function focusFirstEmpty() {
            const firstEmpty = Array.from(inputs).find(input => input.value === '');
            if (firstEmpty) {
                firstEmpty.focus();
            } else {
                inputs[inputs.length - 1].focus();
            }
        }

        // Initial focus
        focusFirstEmpty();
    });
    </script>
</body>

</html>