<?php
require_once __DIR__.'/data/dbconn.php';
session_start();

// 1. Validation des paramètres GET
function validateInputs($email, $token) {
    if (!isset($email) || !isset($token)) {
        header('Location: identification_marche.php#showLoginForm?error=missing_params');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: identification_marche.php#showLoginForm?error=invalid_email');
        exit();
    }

    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        header('Location: identification_marche.php#showLoginForm?error=invalid_token_format');
        exit();
    }
    return true;
}

// 2. Vérification du token en base
function verifyToken($conn, $email, $token) {
    $sql = "SELECT id, expiration_reinitialisation 
            FROM marches 
            WHERE email = ? 
              AND token_reinitialisation = ? 
              AND compte_active = 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email, $token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 3. Vérification de l’expiration
function checkExpiration($conn, $expirationDate, $userId) {
    $now = new DateTime();
    $expiration = new DateTime($expirationDate);

    if ($now > $expiration) {
        $conn->prepare("UPDATE marches 
                        SET token_reinitialisation = NULL, expiration_reinitialisation = NULL 
                        WHERE id = ?")
             ->execute([$userId]);
        return false;
    }
    return true;
}

// 4. Validation mot de passe
function validatePassword($password) {
    if (strlen($password) < 8) return "Le mot de passe doit contenir au moins 8 caractères.";
    return null;
}

// Traitement principal
$error = null;
$success = false;

$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';
validateInputs($email, $token);

$user = verifyToken($conn, $email, $token);
if (!$user) {
    header('Location: identification_marche.php?error=invalid_token');
    exit();
}

if (!checkExpiration($conn, $user['expiration_reinitialisation'], $user['id'])) {
    header('Location: data/renvoyer_code_marche.php?email='.urlencode($email).'&error=token_expired');
    exit();
}

// Si formulaire envoyé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirm_password)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif ($passwordError = validatePassword($password)) {
        $error = $passwordError;
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $conn->beginTransaction();

            $update_sql = "UPDATE marches 
                           SET mot_de_passe = ?, token_reinitialisation = NULL, expiration_reinitialisation = NULL 
                           WHERE id = ?";
            $conn->prepare($update_sql)->execute([$password_hash, $user['id']]);

            $conn->commit();
            $_SESSION['success_password_change'] = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
            header('Location: identification_marche.php#showLoginForm');
            exit();

        } catch (PDOException $e) {
            $conn->rollBack();
            $error = "Une erreur est survenue. Veuillez réessayer.";
            error_log("Password reset error (marché) : " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe - AgriConnect Bénin</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2F855A;
            --primary-hover: #276749;
            --error-color: #E53E3E;
            --error-bg: #FED7D7;
            --success-color: #22543D;
            --success-bg: #C6F6D5;
            --text-color: #2D3748;
            --border-color: #CBD5E0;
            --gray-light: #F5F7FA;
        }
        .logo{
            background: none;;
        }
        body {
            background-color: var(--gray-light);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-color);
            overflow: block;
        }
        
        .container {
            width: 100%;
            max-width: 500px;
            padding: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .logo {
            max-height: 70px;
        }
        
        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(47, 133, 90, 0.1);
        }
        
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50px;
            cursor: pointer;
            color: var(--text-color);
            opacity: 0.6;
        }
        
        .btn {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .btn:hover {
            background-color: var(--primary-hover);
        }
        
        .btn:disabled {
            background-color: #A0AEC0;
            cursor: not-allowed;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: var(--success-bg);
            color: var(--success-color);
        }
        
        .alert-error {
            background-color: var(--error-bg);
            color: var(--error-color);
        }
        
        .password-requirements {
            font-size: 0.875rem;
            color: #718096;
            margin-top: 0.5rem;
        }
        
        .password-requirements ul {
            padding-left: 1.25rem;
            margin: 0.25rem 0;
        }
        
        .password-requirements li.valid {
            color: var(--success-color);
        }
        
        .text-center {
            text-align: center;
            margin-top: 1rem;
        }
        
        .link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo-container">
                <img src="assets/images/logo/Logo_back-off.png" alt="AgriConnect Bénin" class="logo">
            </div>
            
            <h1>Réinitialisation du mot de passe</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="passwordForm">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                
                <div class="form-group">
                    <label for="password" class="form-label">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" class="form-input" required minlength="8">
                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    <div class="password-requirements">
                        <p>Le mot de passe doit contenir :</p>
                        <ul>
                            <li id="req-length">Minimum 8 caractères</li>
                        </ul>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required minlength="8">
                    <i class="fas fa-eye toggle-password" id="toggleConfirmPassword"></i>
                    <div id="confirmError" class="error-message" style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.5rem;"></div>
                </div>
                
                <button type="submit" class="btn" id="submitBtn">Enregistrer le nouveau mot de passe</button>
            </form>
            
            <div class="text-center">
                <a href="identification_marche.php" class="link">Retour à la connexion</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('confirm_password');
            const togglePassword = document.getElementById('togglePassword');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const form = document.getElementById('passwordForm');
            const submitBtn = document.getElementById('submitBtn');
            const confirmError = document.getElementById('confirmError');
            
            // Toggle password visibility
            function toggleVisibility(input, icon) {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
            
            togglePassword.addEventListener('click', () => toggleVisibility(passwordInput, togglePassword));
            toggleConfirmPassword.addEventListener('click', () => toggleVisibility(confirmInput, toggleConfirmPassword));
            
            // Password validation
            function validatePassword() {
                const password = passwordInput.value;
                let isValid = true;
                
                // Check length
                if (password.length >= 8) {
                    document.getElementById('req-length').classList.add('valid');
                } else {
                    document.getElementById('req-length').classList.remove('valid');
                    isValid = false;
                }
            
                
                return isValid;
            }
            
            // Confirm password validation
            function validateConfirmPassword() {
                if (confirmInput.value !== passwordInput.value) {
                    confirmError.textContent = 'Les mots de passe ne correspondent pas';
                    return false;
                }
                confirmError.textContent = '';
                return true;
            }
            
            // Real-time validation
            passwordInput.addEventListener('input', function() {
                validatePassword();
                if (confirmInput.value.length > 0) validateConfirmPassword();
            });
            
            confirmInput.addEventListener('input', validateConfirmPassword);
            
            // Form submission
            form.addEventListener('submit', function(e) {
                const isPasswordValid = validatePassword();
                const isConfirmValid = validateConfirmPassword();
                
                if (!isPasswordValid || !isConfirmValid) {
                    e.preventDefault();
                    if (!isPasswordValid) passwordInput.focus();
                    else confirmInput.focus();
                } else {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement en cours...';
                }
            });
        });
    </script>
</body>
</html>