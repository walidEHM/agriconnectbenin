<?php
// 1. Initialisation sécurisée de session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

// 2. Vérification d'authentification
function verifyAdminAuth() {
    if (empty($_SESSION['admin']) || $_SESSION['admin'] !== true || 
        empty($_SESSION['admin_id']) || empty($_SESSION['admin_email'])) {
        header("Location: ../authform.php");
        exit;
    }
}
verifyAdminAuth();

// 3. Connexion DB
require_once __DIR__ . '/../../../data/dbconn.php';

// 4. Traitement du formulaire
$errors = [];
$success = false;
$adminInfo = [];

try {
    $currentAdmin = $conn->prepare("SELECT email, password_hash FROM admins WHERE id = ?");
    $currentAdmin->execute([$_SESSION['admin_id']]);
    $adminInfo = $currentAdmin->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $current_password = trim($_POST['current_password'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        // Validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Email invalide";
        }

        if (!empty($new_password)) {
            if (empty($current_password)) {
                $errors['current_password'] = "Mot de passe actuel requis";
            } elseif (!password_verify($current_password, $adminInfo['password_hash'])) {
                $errors['current_password'] = "Mot de passe incorrect";
            }

            if (strlen($new_password) < 8) {
                $errors['new_password'] = "8 caractères minimum";
            } elseif ($new_password !== $confirm_password) {
                $errors['confirm_password'] = "Mots de passe différents";
            }
        }

        if (empty($errors)) {
            $conn->beginTransaction();

            // Mise à jour mot de passe si besoin
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $conn->prepare("UPDATE admins SET password_hash = ? WHERE id = ?")
                     ->execute([$hashed_password, $_SESSION['admin_id']]);
            }

            // Mise à jour email si modifié
            if ($email !== $adminInfo['email']) {
                $conn->prepare("UPDATE admins SET email = ? WHERE id = ?")
                     ->execute([$email, $_SESSION['admin_id']]);
                $_SESSION['admin_email'] = $email;
            }

            $conn->commit();
            $success = true;
            
            // Rafraîchir les données
            $currentAdmin->execute([$_SESSION['admin_id']]);
            $adminInfo = $currentAdmin->fetch(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    $errors['general'] = "Erreur base de données";
    error_log("DB Error in parametres.php: " . $e->getMessage());
} catch (Exception $e) {
    $errors['general'] = "Erreur système";
    error_log("System Error in parametres.php: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres Administrateur - AgriConnect</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    :root {
        --vert-foret: #2F855A;
        --orange-terre: #ED8936;
        --gris-doux: #718096;
        --fond-clair: #f4f4f4;
        --vert-vif: #38A169;
        --rouge: #E53E3E;
        --gris-fonce: #2D3748;
        --gris-clair: #E2E8F0;
    }

    .logo-container {
        text-align: center;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'poppins';
        background-color: var(--fond-clair);
        color: var(--gris-fonce);
        line-height: 1.5;
        padding: 2rem;
    }

    .settings-container {
        max-width: 600px;
        margin: 2rem auto;
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .settings-title {
        font-family: 'Poppins', sans-serif;
        font-size: 24px;
        color: var(--vert-foret);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .error-border {
        border-color: var(--rouge) !important;
        box-shadow: 0 0 0 2px rgba(229, 62, 62, 0.2);
    }

    .error-message {
        color: var(--rouge);
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: block;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--gris-fonce);
    }

    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid var(--gris-clair);
        border-radius: 8px;
        font-family: 'Inter', sans-serif;
        transition: border-color 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--vert-foret);
    }

    .password-toggle {
        position: relative;
    }

    .password-toggle-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gris-doux);
        cursor: pointer;
    }

    .btn-save {
        background-color: var(--vert-foret);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-save:hover {
        background-color: #276749;
    }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .alert-success {
        background-color: #C6F6D5;
        color: #22543D;
    }

    .alert-danger {
        background-color: #FED7D7;
        color: #9B2C2C;
    }

    .error-message {
        color: var(--rouge);
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    @media (max-width: 768px) {
        body {
            padding: 1rem;
        }

        .settings-container {
            padding: 1.5rem;
        }
    }
    </style>
</head>

<body>
    <div class="settings-container">
        <div class="logo-container">
            <img src="../../../assets/images/logo/Logo_back-off.png" alt="Logo AgriConnect BENIN" height="50px"
                loading="lazy">
        </div>
        <h1 class="settings-title"><i class="fas fa-cog"></i> Paramètres du compte</h1>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Mise à jour réussie
        </div>
        <?php elseif (!empty($errors['general'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['general']) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                    value="<?= htmlspecialchars($adminInfo['email'] ?? '') ?>" required>
                <?php if (!empty($errors['email'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['email']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="current_password">Mot de passe actuel</label>
                <div class="password-toggle">
                    <input type="password" id="current_password" name="current_password" class="form-control">
                    <i class="fas fa-eye password-toggle-icon"></i>
                </div>
                <?php if (!empty($errors['current_password'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['current_password']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="new_password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                <div class="password-toggle">
                    <input type="password" id="new_password" name="new_password" class="form-control">
                    <i class="fas fa-eye password-toggle-icon"></i>
                </div>
                <?php if (!empty($errors['new_password'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['new_password']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                <div class="password-toggle">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                    <i class="fas fa-eye password-toggle-icon"></i>
                </div>
                <?php if (!empty($errors['confirm_password'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> Enregistrer
            </button>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sécurité : Vérification que les éléments existent avant de les manipuler
        const form = document.querySelector('form');
        if (!form) return;

        // Fonction utilitaire pour afficher les erreurs
        const showError = (input, message) => {
            const formGroup = input.closest('.form-group');
            if (!formGroup) return;

            let errorElement = formGroup.querySelector('.error-message');
            if (!errorElement) {
                errorElement = document.createElement('span');
                errorElement.className = 'error-message';
                formGroup.appendChild(errorElement);
            }
            errorElement.textContent = message;
            input.classList.add('error-border');
        };

        // Fonction pour effacer les erreurs
        const clearError = (input) => {
            const formGroup = input.closest('.form-group');
            if (!formGroup) return;

            const errorElement = formGroup.querySelector('.error-message');
            if (errorElement) errorElement.remove();
            input.classList.remove('error-border');
        };

        // Basculer la visibilité des mots de passe avec vérifications
        document.querySelectorAll('.password-toggle-icon').forEach(icon => {
            icon.addEventListener('click', function() {
                const input = this.previousElementSibling;
                if (!input || input.type === undefined) return;

                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.replace('fa-eye', 'fa-eye-slash');
                    // Sécurité : Désactiver l'autocomplétion en mode visible
                    input.setAttribute('autocomplete', 'off');
                } else {
                    input.type = 'password';
                    this.classList.replace('fa-eye-slash', 'fa-eye');
                    input.setAttribute('autocomplete', 'current-password');
                }
            });
        });

        // Validation en temps réel du mot de passe
        const passwordInputs = ['current_password', 'new_password', 'confirm_password'];
        passwordInputs.forEach(id => {
            const input = document.getElementById(id);
            if (!input) return;

            input.addEventListener('input', function() {
                clearError(this);

                if (this.id === 'new_password' && this.value.length > 0 && this.value.length <
                    8) {
                    showError(this, 'Le mot de passe doit contenir au moins 8 caractères');
                }

                if (this.id === 'confirm_password' && this.value !== document.getElementById(
                        'new_password').value) {
                    showError(this, 'Les mots de passe ne correspondent pas');
                }
            });
        });

        // Validation de l'email en temps réel
        const emailInput = document.getElementById('email');
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                clearError(this);
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!emailRegex.test(this.value)) {
                    showError(this, 'Veuillez entrer une adresse email valide');
                }
            });
        }

        // Validation finale avant soumission
        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Vérification de l'email
            const email = document.getElementById('email');
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                showError(email, 'Email invalide');
                isValid = false;
            }

            // Vérification des mots de passe
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');

            if (newPassword && newPassword.value.length > 0) {
                if (newPassword.value.length < 8) {
                    showError(newPassword, '8 caractères minimum');
                    isValid = false;
                }

                if (confirmPassword && newPassword.value !== confirmPassword.value) {
                    showError(confirmPassword, 'Les mots de passe doivent correspondre');
                    isValid = false;
                }

                const currentPassword = document.getElementById('current_password');
                if (currentPassword && currentPassword.value.length === 0) {
                    showError(currentPassword, 'Le mot de passe actuel est requis');
                    isValid = false;
                }
            }

            // Empêcher la soumission si invalide
            if (!isValid) {
                e.preventDefault();

                // Scroll vers la première erreur
                const firstError = document.querySelector('.error-border');
                if (firstError) {
                    firstError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }

                // Feedback utilisateur
                const errorNotification = document.createElement('div');
                errorNotification.className = 'alert alert-danger';
                errorNotification.innerHTML =
                    '<i class="fas fa-exclamation-circle"></i> Veuillez corriger les erreurs dans le formulaire';
                form.prepend(errorNotification);

                // Supprimer la notification après 5s
                setTimeout(() => {
                    errorNotification.remove();
                }, 5000);
            }
        });

        // Sécurité : Désactiver le copier-coller dans le champ de confirmation
        const confirmPassword = document.getElementById('confirm_password');
        if (confirmPassword) {
            confirmPassword.addEventListener('paste', function(e) {
                e.preventDefault();
                showError(this, 'Le collage est désactivé pour la confirmation');
            });
        }
    });
    </script>
</body>

</html>