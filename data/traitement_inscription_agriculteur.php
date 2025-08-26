<?php
require_once __DIR__ . '/../src/vendor/autoload.php';
require_once __DIR__ . '/../src/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../src/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../src/vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/dbconn.php';
date_default_timezone_set('Africa/Porto-Novo');


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $prenom = trim($_POST['prenom']);
    $nom = trim($_POST['nom']);
    $nom_complet = $prenom . ' ' . $nom;
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    if (empty($prenom) || empty($nom) || empty($email) || empty($mot_de_passe)) {
        die("Tous les champs sont obligatoires.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Adresse email invalide.");
    }

    if (strlen($mot_de_passe) < 8) {
        die("Le mot de passe doit contenir au moins 8 caractères.");
    }

    try {
        $stmt = $conn->prepare("SELECT id, compte_active FROM agriculteurs WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingUser['compte_active'] == 1) {
                echo "<script>
                        alert('Cette adresse email est déjà utilisée par un compte actif.');
                        window.location.href = 'identification_agriculteur.php';
                      </script>";
                    exit();
            } else {
                $conn->prepare("DELETE FROM agriculteurs WHERE id = ?")->execute([$existingUser['id']]);
                
                echo "<script>
                        alert('Un nouveau lien d\\'activation a été envoyé à votre adresse email.');
                      </script>";
            }
        }

        // Générer un token et hacher le mot de passe
        $token_verification = bin2hex(random_bytes(32));
        $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);

        // Insertion
        $stmt = $conn->prepare("INSERT INTO agriculteurs (nom_complet, prenom, nom, email, mot_de_passe, token_verification) 
                               VALUES (:nom_complet, :prenom, :nom, :email, :mot_de_passe, :token_verification)");
        $stmt->bindParam(':nom_complet', $nom_complet);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mot_de_passe', $hashed_password);
        $stmt->bindParam(':token_verification', $token_verification);
        $stmt->execute();

        $agriculteur_id = $conn->lastInsertId();

        // Envoi de l'email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'elwalid2008@gmail.com';
            $mail->Password = 'nwki cznq bqij mkou';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Configuration pour des emails uniques
            $mail->MessageID = md5(uniqid()) . '@agriconnectbenin.com'; // Message-ID unique
            $mail->addCustomHeader('X-Entity-Ref-ID', uniqid()); // Header anti-threading pour Outlook
            $mail->clearReplyTos(); // Supprime les Reply-To existants
            $mail->addReplyTo('no-reply-' . uniqid() . '@agriconnectbenin.com', 'No Reply'); // Reply-To unique

            $mail->setFrom('elwalid2008@gmail.com', 'AgriConnect Bénin');
            $mail->addAddress($email);

            $verification_link = "http://localhost/projet%20de%20soutenance/verification_agriculteur.php?token=$token_verification&id=$agriculteur_id";

            $mail->isHTML(true);
            // Sujet unique (ajout d'un timestamp si nécessaire)
            $mail->Subject = "=?UTF-8?B?" . base64_encode('Vérification de votre compte AgriConnect - ' . date('Y-m-d H:i:s')) . "?=";
            $mail->Body = "
                <div style='font-family: poppins; background-color: #f9f9f9; padding: 20px;'>
                    <div style='max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.08);'>
                        <!-- Header -->
                        <div style='background-color: #2F855A; padding: 24px; text-align: center; color: #ffffff;'>
                            <h1 style='margin: 0; font-size: 26px;'>Bienvenue sur AgriConnect Bénin</h1>
                        </div>
                        <!-- Body -->
                        <div style='padding: 30px; color: #333333;'>
                            <p style='font-size: 16px;'>Bonjour <strong>$prenom</strong>,</p>
                            <p style='font-size: 16px; line-height: 1.6;'>Merci de vous être inscrit sur notre plateforme dédiée aux agriculteurs du Bénin. Pour confirmer votre compte, cliquez simplement sur le bouton ci-dessous :</p>
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='$verification_link' style='background-color: #2F855A; color: #ffffff; padding: 14px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: inline-block;'>Vérifier mon compte</a>
                            </div>
                            <p style='font-size: 14px; color: #555;'>Si vous n'avez pas fait cette demande, vous pouvez ignorer ce message en toute sécurité.</p>
                            <p style='margin-top: 40px; font-size: 14px;'>Cordialement,<br><strong>L'équipe AgriConnect Bénin</strong></p>
                        </div>
                        <!-- Footer -->
                        <div style='background-color: #f0f0f0; padding: 18px; text-align: center; font-size: 12px; color: #777;'>
                            &copy; ".date('Y')." AgriConnect Bénin &middot; Tous droits réservés
                        </div>
                    </div>
                </div>
            ";

            $mail->AltBody = "Merci de vous être inscrit. Copiez ce lien pour vérifier votre compte : $verification_link";

            $mail->send();

            header('Location: ../inscription_agriculteur_success.php');
            exit();

        } catch (Exception $e) {
            $conn->prepare("DELETE FROM agriculteurs WHERE id = ?")->execute([$agriculteur_id]);
            die("L'email de vérification n'a pas pu être envoyé. Erreur: " . $mail->ErrorInfo);
        }

    } catch (PDOException $e) {
        die("Erreur de base de données : " . $e->getMessage());
    }
} else {
    header('Location: index.php');
    exit();
}