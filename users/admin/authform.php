<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - AgriConnect BENIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-container">
                <img src="../../assets/images/logo/Logo_back-off.png" alt="Logo AgriConnect BENIN" height="55">
            </div>
            <h1>Espace Administrateur</h1>
            <p>Connectez-vous pour accéder au tableau de bord</p>
        </div>

        <div id="error-notification" class="notification notification-error" style="display: none;">
            <i class="fas fa-exclamation-circle"></i>
            <span id="error-message"></span>
        </div>

        <form id="login-form" action="data/traitement_authform.php" method="POST">
            <div class="form-group">
                <label for="username" class="form-label">Nom d'utilisateur ou email</label>
                <input type="text" id="username" name="username" class="form-input" required
                    placeholder="Entrez votre identifiant">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-input" required
                    placeholder="Entrez votre mot de passe">
                <i class="fas fa-eye password-toggle" id="toggle-password"></i>
            </div>

            <button type="submit" class="button button-primary" id="submit-btn">
                <span id="btn-text">Se connecter</span>
                <div class="loading-spinner" id="loading-spinner"></div>
            </button>
        </form>

        <p class="footer-text">© 2025 AgriConnect BENIN. Tous droits réservés.</p>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }

        const loginForm = document.getElementById('login-form');
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submit-btn');
            const btnText = document.getElementById('btn-text');
            const loadingSpinner = document.getElementById('loading-spinner');
            const errorDisplay = document.getElementById('error-notification');
            const errorMessage = document.getElementById('error-message');

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            if (!username || !password) {
                showError('Veuillez remplir tous les champs');
                return;
            }

            submitBtn.disabled = true;
            btnText.textContent = 'Connexion en cours...';
            loadingSpinner.style.display = 'inline-block';
            errorDisplay.style.display = 'none';

            try {
                const response = await fetch(loginForm.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        username,
                        password
                    })
                });

                const raw = await response.text();
                console.log("Réponse brute du serveur :", raw);

                try {
                    const data = JSON.parse(raw);

                    if (data.success) {
                        window.location.href = 'index.php';
                    } else {
                        showError(data.message || 'Échec de la connexion');
                    }
                } catch (parseError) {
                    console.error("Erreur de parsing JSON :", parseError);
                    showError(
                        "Réponse invalide du serveur. Vérifiez les erreurs PHP dans la console."
                        );
                }



            } catch (error) {
                console.error("Erreur réseau/fetch :", error);
                showError(error.message || 'Erreur réseau');
            } finally {
                submitBtn.disabled = false;
                btnText.textContent = 'Se connecter';
                loadingSpinner.style.display = 'none';
            }

            function showError(message) {
                errorMessage.textContent = message;
                errorDisplay.style.display = 'flex';
                setTimeout(() => errorDisplay.style.display = 'none', 5000);
            }
        });
    });
    </script>
</body>

</html>