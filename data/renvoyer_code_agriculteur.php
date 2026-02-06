<?php
require_once __DIR__ . '/../src/vendor/autoload.php';
require_once __DIR__ . '/../src/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../src/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../src/vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/dbconn.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Validation initiale
if (!isset($_GET['email'])) {
    header('Location: ../verifier_code_de_mot_de_passe_oublie_agriculteur.php?error=email_requis');
    exit();
}

$email = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    header('Location: ../verifier_code_de_mot_de_passe_oublie_agriculteur.php?error=email_invalide');
    exit();
}

try {
    // Vérification utilisateur
    $stmt = $conn->prepare("SELECT id, prenom, compte_active FROM agriculteurs WHERE email = ? AND compte_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Réponse générique même si l'email n'existe pas (sécurité)
    if (!$user) {
        header('Location: ../verifier_code_de_mot_de_passe_oublie_agriculteur.php?email='.urlencode($email).'&success=1');
        exit();
    }

    // Génération du nouveau code
    $new_code = sprintf("%06d", random_int(0, 999999));
    $new_expiration = (new DateTime())->add(new DateInterval('PT15M'))->format('Y-m-d H:i:s');

    // Mise à jour en base
    $update_stmt = $conn->prepare("UPDATE agriculteurs SET token_verification = ?, expiration_token = ? WHERE id = ?");
    $update_stmt->execute([$new_code, $new_expiration, $user['id']]);

    $mail = new PHPMailer(true);
    
    // Configuration SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = ''; // À externaliser dans un fichier config
    $mail->Password = ''; // À externaliser
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    
    // En-têtes uniques pour chaque email
    $mail->MessageID = md5(uniqid()) . '@agriconnectbenin.com';
    $mail->addCustomHeader('X-Entity-Ref-ID', uniqid());
    $mail->clearReplyTos();
    $mail->addReplyTo('no-reply-' . uniqid() . '@agriconnectbenin.com', 'No Reply');
    
    // Expéditeur et destinataire
    $mail->setFrom('elwalid2008@gmail.com', 'AgriConnect Bénin', 0); // Le 0 évite l'overwrite
    $mail->addAddress($email);
    
    // Contenu de l'email
    $mail->isHTML(true);
    $mail->Subject = "=?UTF-8?B?" . base64_encode('Nouveau code de vérification - ' . date('d/m/Y H:i')) . "?=";
    
    // Corps du message (version professionnelle)
    $mail->Body = "
    <div style='font-family: poppins; max-width: 600px; margin: auto; border: 1px solid #e0e0e0;'>
        <div style='background-color: #2F855A; padding: 20px; color: white; text-align: center;'>
            <h2>Votre nouveau code de vérification</h2>
        </div>
        <div style='padding: 30px;'>
            <p>Bonjour {$user['prenom']},</p>
            <p>Voici votre nouveau code de vérification :</p>
            <div style='background-color: #f5f5f5; padding: 15px; text-align: center; margin: 20px 0; 
                        font-size: 24px; letter-spacing: 3px; font-weight: bold; color: #2F855A;'>
                $new_code
            </div>
            <p>Ce code est valable jusqu'à ".date('H:i', strtotime($new_expiration))." (15 minutes).</p>
            <p>Si vous n'avez pas demandé ce code, veuillez ignorer cet email.</p>
        </div>
        <div style='background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px;'>
            © ".date('Y')." AgriConnect Bénin - Tous droits réservés
        </div>
    </div>";

    $mail->AltBody = "Votre code de vérification AgriConnect est : $new_code\nValable jusqu'à ".date('H:i', strtotime($new_expiration));

    // Envoi avec timeout
    $mail->Timeout = 15; // 15 secondes max pour l'envoi
    $mail->send();
    if (isset($_GET['error']) && $_GET['error'] === 'token_expired') {
    header('Location: ../verifier_code_de_mot_de_passe_oublie_agriculteur.php?email=' . urlencode($email) . '&error=token_expired');
    exit();
}

header('Location: ../verifier_code_de_mot_de_passe_oublie_agriculteur.php?email=' . urlencode($email) . '&success=1');
exit();


} catch (Exception $e) {
    error_log("Email error: ".$e->getMessage());
    header('Location: ../verifier_code_de_mot_de_passe_oublie_agriculteur.php?email='.urlencode($email).'&error=erreur_systeme');
    exit();
}
