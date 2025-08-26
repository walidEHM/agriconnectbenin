<?php
session_start();

// Vérification de sécurité renforcée
if (!isset($_SESSION['marche_id'], $_SESSION['marche_connecte']) || $_SESSION['marche_connecte'] !== true) {
    header("Location: ../../index.php");
    exit();
}

require_once __DIR__ . '/../../data/dbconn.php';
$marche_id = $_SESSION['marche_id'];
$message = "";
$message_pwd = "";

// Traitement du formulaire principal avec validation améliorée
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['change_password'])) {
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    // Validation avancée
    $errors = [];
    if (empty($nom) || strlen($nom) > 100) {
        $errors[] = "Le nom doit contenir entre 1 et 100 caractères";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    }

    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("UPDATE marches SET nom = ?, email = ? WHERE id = ?");
            $stmt->execute([$nom, $email, $marche_id]);
            
            if ($stmt->rowCount() > 0) {
                $message = "<div class='alert alert-success'>Profil mis à jour avec succès.</div>";
            } else {
                $message = "<div class='alert alert-info'>Aucune modification détectée.</div>";
            }
            
            $conn->commit();
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Erreur mise à jour profil: " . $e->getMessage());
            $message = "<div class='alert alert-danger'>Une erreur technique est survenue.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>" . implode("<br>", $errors) . "</div>";
    }
}

// Traitement sécurisé du changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    if (empty($old) || empty($new) || empty($confirm)) {
        $errors[] = "Tous les champs sont obligatoires";
    } elseif (strlen($new) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    } elseif ($new !== $confirm) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }

    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            
            // Vérification de l'ancien mot de passe
            $stmt = $conn->prepare("SELECT mot_de_passe FROM marches WHERE id = ?");
            $stmt->execute([$marche_id]);
            
            if ($stmt->rowCount() === 1) {
                $hash = $stmt->fetchColumn();
                
                if (!password_verify($old, $hash)) {
                    $errors[] = "Ancien mot de passe incorrect";
                } else {
                    // Vérification que le nouveau mot de passe est différent
                    if (password_verify($new, $hash)) {
                        $errors[] = "Le nouveau mot de passe doit être différent de l'ancien";
                    } else {
                        $new_hash = password_hash($new, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE marches SET mot_de_passe = ? WHERE id = ?");
                        
                        if ($stmt->execute([$new_hash, $marche_id])) {
                            $message_pwd = "<div class='alert alert-success'>Mot de passe modifié avec succès.</div>";
                        } else {
                            $errors[] = "Erreur lors de la modification";
                        }
                    }
                }
            } else {
                $errors[] = "Erreur d'authentification";
            }
            
            if (!empty($errors)) {
                $conn->rollBack();
                $message_pwd = "<div class='alert alert-danger'>" . implode("<br>", $errors) . "</div>";
            } else {
                $conn->commit();
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Erreur changement mot de passe: " . $e->getMessage());
            $message_pwd = "<div class='alert alert-danger'>Une erreur technique est survenue.</div>";
        }
    } else {
        $message_pwd = "<div class='alert alert-danger'>" . implode("<br>", $errors) . "</div>";
    }
}

// Récupération sécurisée des infos du marché
try {
    $stmt = $conn->prepare("SELECT nom, email, date_inscription FROM marches WHERE id = ?");
    $stmt->execute([$marche_id]);
    
    if ($stmt->rowCount() === 1) {
        $marche = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Redirection si le compte n'existe plus
        header("Location: deconnexion.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Erreur récupération profil: " . $e->getMessage());
    $marche = ['nom' => '', 'email' => '', 'date_inscription' => date('Y-m-d')];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - Marché | AgriConnect</title>
    <link rel="stylesheet" href="assets/css/chat.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #2F855A;
            --primary-light: #38A169;
            --danger-color: #C53030;
            --text-color: #2D3748;
            --text-light: #718096;
            --border-color: #E2E8F0;
            --bg-light: #F7FAFC;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #F8F9FA;
            color: var(--text-color);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        
        .profile-container {
            max-width: 500px;
            margin: 100px auto 40px;
            background: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 2rem;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 3px solid var(--primary-color);
            box-shadow: 0 4px 12px rgba(47, 133, 90, 0.15);
        }
        
        .profile-title {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .profile-date {
            color: var(--text-light);
            font-size: 0.875rem;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(47, 133, 90, 0.2);
        }
        
        .form-control[readonly] {
            background-color: var(--bg-light);
            cursor: not-allowed;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-light);
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .alert {
            padding: 0.875rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: #F0FFF4;
            color: #22543D;
            border-left: 4px solid #38A169;
        }
        
        .alert-danger {
            background-color: #FFF5F5;
            color: #822727;
            border-left: 4px solid #C53030;
        }
        
        .alert-info {
            background-color: #EBF8FF;
            color: #2B6CB0;
            border-left: 4px solid #4299E1;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            padding: 2rem;
            position: relative;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            font-size: 1.5rem;
            color: var(--text-light);
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .close-modal:hover {
            color: var(--primary-color);
        }
        
        .modal-title {
            font-size: 1.25rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }
        
        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            background-color: var(--border-color);
            border-radius: 2px;
            overflow: hidden;
        }
        
        .strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s, background-color 0.3s;
        }
        
        @media (max-width: 576px) {
            .profile-container {
                margin: 80px auto 20px;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <img src="assets/images/profiles/user_default_marche.jpg" alt="Photo de profil" class="profile-avatar">
            <h1 class="profile-title">Mon Profil</h1>
            <div class="profile-date">
                <i class="bi bi-calendar-check"></i> Inscrit le <?= date('d/m/Y', strtotime($marche['date_inscription'])) ?>
            </div>
        </div>
        
        <div id="profilMessage"></div>
        
        <form id="profilForm" autocomplete="off">
            <div class="form-group">
                <label for="nom" class="form-label">Nom du marché</label>
                <input type="text" id="nom" name="nom" class="form-control" 
                       value="<?= htmlspecialchars($marche['nom'], ENT_QUOTES) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">Adresse email</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?= htmlspecialchars($marche['email'], ENT_QUOTES) ?>" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block" id="profilSubmit">
                <i class="bi bi-save"></i> Enregistrer les modifications
            </button>
        </form>
        
        <button type="button" id="openPwdModal" class="btn btn-primary btn-block" style="margin-top: 1rem;">
            <i class="bi bi-shield-lock"></i> Modifier le mot de passe
        </button>
        
        <!-- Modal de changement de mot de passe -->
        <div id="pwdModal" class="modal">
            <div class="modal-content">
                <span class="close-modal" id="closePwdModal">&times;</span>
                <h3 class="modal-title">
                    <i class="bi bi-key"></i> Changer le mot de passe
                </h3>
                
                <div id="pwdMessage"></div>
                
                <form id="pwdForm" autocomplete="off">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label for="old_password" class="form-label">Ancien mot de passe</label>
                        <input type="password" id="old_password" name="old_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                        <div class="password-strength">
                            <div class="strength-meter" id="strengthMeter"></div>
                        </div>
                        <small class="text-muted">Minimum 8 caractères</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <div id="pwdError" class="alert alert-danger" style="display: none;"></div>
                    
                    <button type="submit" class="btn btn-primary btn-block" id="pwdSubmit">
                        <i class="bi bi-check-circle"></i> Valider
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Gestion de la modale
    const modal = document.getElementById('pwdModal');
    const openBtn = document.getElementById('openPwdModal');
    const closeBtn = document.getElementById('closePwdModal');
    
    if (openBtn && modal && closeBtn) {
        openBtn.addEventListener('click', () => {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
        
        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
        
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    }
    
    // AJAX profil update
    const profilForm = document.getElementById('profilForm');
    const profilMessage = document.getElementById('profilMessage');
    const profilSubmit = document.getElementById('profilSubmit');
    if (profilForm) {
        profilForm.addEventListener('submit', function(e) {
            e.preventDefault();
            profilMessage.innerHTML = '';
            profilSubmit.disabled = true;
            profilSubmit.innerHTML = '<i class="bi bi-arrow-repeat"></i> Traitement...';
            fetch('data/profil_update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    nom: document.getElementById('nom').value,
                    email: document.getElementById('email').value
                })
            })
            .then(res => res.json())
            .then(data => {
                profilMessage.innerHTML = `<div class="alert ${data.success ? 'alert-success' : (data.message.includes('modification') ? 'alert-info' : 'alert-danger')}">${data.message}</div>`;
                profilSubmit.disabled = false;
                profilSubmit.innerHTML = '<i class="bi bi-save"></i> Enregistrer les modifications';
            })
            .catch(() => {
                profilMessage.innerHTML = '<div class="alert alert-danger">Erreur réseau.</div>';
                profilSubmit.disabled = false;
                profilSubmit.innerHTML = '<i class="bi bi-save"></i> Enregistrer les modifications';
            });
        });
    }
    
    // Password strength
    const newPwdInput = document.getElementById('new_password');
    const confirmPwdInput = document.getElementById('confirm_password');
    const strengthMeter = document.getElementById('strengthMeter');
    if (newPwdInput && strengthMeter) {
        newPwdInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Longueur minimale
            if (password.length >= 8) strength += 1;
            
            // Contient des lettres minuscules et majuscules
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
            
            // Contient des chiffres
            if (/\d/.test(password)) strength += 1;
            
            // Contient des caractères spéciaux
            if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
            
            // Mise à jour de la barre de force
            const width = strength * 25;
            let color = '#C53030'; // Rouge
            
            if (strength >= 3) color = '#38A169'; // Vert
            else if (strength >= 2) color = '#D69E2E'; // Jaune
            
            strengthMeter.style.width = `${width}%`;
            strengthMeter.style.backgroundColor = color;
        });
    }
    
    // AJAX password update
    const pwdForm = document.getElementById('pwdForm');
    const pwdMessage = document.getElementById('pwdMessage');
    const pwdSubmit = document.getElementById('pwdSubmit');
    if (pwdForm) {
        pwdForm.addEventListener('submit', function(e) {
            e.preventDefault();
            pwdMessage.innerHTML = '';
            document.getElementById('pwdError').style.display = 'none';
            pwdSubmit.disabled = true;
            pwdSubmit.innerHTML = '<i class="bi bi-arrow-repeat"></i> Traitement...';
            fetch('data/profil_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    old_password: document.getElementById('old_password').value,
                    new_password: document.getElementById('new_password').value,
                    confirm_password: document.getElementById('confirm_password').value
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    pwdMessage.innerHTML = `<div class='alert alert-success'>${data.message}</div>`;
                    pwdForm.reset();
                    strengthMeter.style.width = '0';
                } else {
                    pwdMessage.innerHTML = `<div class='alert alert-danger'>${data.message}</div>`;
                }
                pwdSubmit.disabled = false;
                pwdSubmit.innerHTML = '<i class="bi bi-check-circle"></i> Valider';
            })
            .catch(() => {
                pwdMessage.innerHTML = '<div class="alert alert-danger">Erreur réseau.</div>';
                pwdSubmit.disabled = false;
                pwdSubmit.innerHTML = '<i class="bi bi-check-circle"></i> Valider';
            });
        });
    }
    </script>
</body>
</html>