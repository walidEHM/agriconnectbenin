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
    $nom_marche = trim($_POST['nom_marche']);
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validation des données
    if (empty($nom_marche) || empty($email) || empty($mot_de_passe) || empty($confirm_password)) {
        die("Tous les champs sont obligatoires.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Adresse email invalide.");
    }

    if (strlen($mot_de_passe) < 8) {
        die("Le mot de passe doit contenir au moins 8 caractères.");
    }

    if ($mot_de_passe !== $confirm_password) {
        die("Les mots de passe ne correspondent pas.");
    }

    try {
        // Vérifier si l'email existe déjà
        $stmt = $conn->prepare("SELECT id FROM marches WHERE email = :email");
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
                $conn->prepare("DELETE FROM marches WHERE id = ?")->execute([$existingUser['id']]);
                
                echo "<script>
                        alert('Un nouveau lien d\\'activation a été envoyé à votre adresse email.');
                      </script>";
            }
        }

        // Hacher le mot de passe
        $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);

        // Générer un token de vérification
        $token_verification = bin2hex(random_bytes(32));

        // Insertion dans la base de données
        $stmt = $conn->prepare("INSERT INTO marches (nom, email, mot_de_passe, token_verification, date_inscription) 
                               VALUES (:nom, :email, :mot_de_passe, :token_verification, NOW())");
        $stmt->bindParam(':nom', $nom_marche);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mot_de_passe', $hashed_password);
        $stmt->bindParam(':token_verification', $token_verification);
        $stmt->execute();

        $marche_id = $conn->lastInsertId();

        // Envoi de l'email de confirmation
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
            $mail->MessageID = md5(uniqid()) . '@agriconnectbenin.com';
            $mail->addCustomHeader('X-Entity-Ref-ID', uniqid());
            $mail->clearReplyTos();
            $mail->addReplyTo('no-reply-' . uniqid() . '@agriconnectbenin.com', 'No Reply');

            $mail->setFrom('elwalid2008@gmail.com', 'AgriConnect Bénin');
            $mail->addAddress($email);

            $verification_link = "http://localhost/projet%20de%20soutenance/verification_marche.php?token=$token_verification&id=$marche_id";

            $mail->isHTML(true);
            $mail->Subject = "=?UTF-8?B?" . base64_encode('Vérification de votre compte Marché AgriConnect - ' . date('Y-m-d H:i:s')) . "?=";
            $mail->Body = "
                <div style='font-family: poppins; background-color: #f9f9f9; padding: 20px;'>
                    <div style='max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.08);'>
                        <!-- Header -->
                        <div style='background-color: #2F855A; padding: 24px; text-align: center; color: #ffffff;'>
                            <h1 style='margin: 0; font-size: 26px;'>Bienvenue sur AgriConnect Bénin</h1>
                        </div>
                        <!-- Body -->
                        <div style='padding: 30px; color: #333333;'>
                            <p style='font-size: 16px;'>Bonjour <strong>$nom_marche</strong>,</p>
                            <p style='font-size: 16px; line-height: 1.6;'>Merci d'avoir créé un compte Marché sur notre plateforme. Pour activer votre compte, cliquez simplement sur le bouton ci-dessous :</p>
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='$verification_link' style='background-color: #2F855A; color: #ffffff; padding: 14px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: inline-block;'>Activer mon compte</a>
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

            $mail->AltBody = "Merci d'avoir créé un compte Marché. Copiez ce lien pour activer votre compte : $verification_link";

            $mail->send();

            // Redirection vers une page de succès
            $_SESSION['success_message'] = "Un email de confirmation a été envoyé à $email. Veuillez vérifier votre boîte mail pour activer votre compte.";
            header('Location: ../inscription_marche_success.php');
            exit();

        } catch (Exception $e) {
            // En cas d'erreur d'envoi d'email, supprimer l'utilisateur créé
            $conn->prepare("DELETE FROM marches WHERE id = ?")->execute([$marche_id]);
            die("L'email de vérification n'a pas pu être envoyé. Erreur: " . $mail->ErrorInfo);
        }

    } catch (PDOException $e) {
        die("Erreur de base de données : " . $e->getMessage());
    }
} else {
    header('Location: ../index.php');
    exit();
}