<?php
session_start();
if (!isset($_SESSION['token_admin_verification']) || $_SESSION['admin'] !== true  ) {
    // Redirection vers la page de login
    header("Location: ../authform.php");
    exit();
}
?>
<?php
require_once __DIR__ . '/../../../src/vendor/autoload.php';
require_once __DIR__ . '/../../../src/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../../src/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../../src/vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../../data/dbconn.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Vérification que l'email est disponible en session
if (empty($_SESSION['admin_email'])) {
    header("Location: authform.php");
    exit();
}

$email = 'elwalid2008@gmail.com';
$prenom = $_SESSION['admin_nom'] ?? 'Administrateur'; // Valeur par défaut

try {
    if (empty($_SESSION['token_admin_verification'])) {
        throw new Exception("Token de vérification manquant");
    }

    $new_code = $_SESSION['token_admin_verification'];
    $new_expiration = (new DateTime())->add(new DateInterval('PT15M'))->format('Y-m-d H:i:s');

    $mail = new PHPMailer(true);

    // Configuration SMTP (à externaliser dans un fichier de config)
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'elwalid2008@gmail.com'; // À mettre dans un fichier config
    $mail->Password = 'nwki cznq bqij mkou'; // À mettre dans un fichier config sécurisé
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    // En-têtes
    $mail->MessageID = md5(uniqid()) . '@agriconnectbenin.com';
    $mail->addCustomHeader('X-Entity-Ref-ID', uniqid());
    $mail->clearReplyTos();
    $mail->addReplyTo('no-reply-' . uniqid() . '@agriconnectbenin.com', 'No Reply');

    // Expéditeur et destinataire
    $mail->setFrom('elwalid2008@gmail.com', 'AgriConnect Bénin', 0);
    $mail->addAddress($email);

    // Sujet et contenu
    $mail->isHTML(true);
    $mail->Subject = "Accès au bord administrateur - " . date('d/m/Y H:i');

    $mail->Body = "
        <div style='max-width: 600px; margin: auto; border: 1px solid #e0e0e0; font-family: Arial, Helvetica, sans-serif;'>
            <div style='background-color: #2F855A; padding: 20px; color: white; text-align: center;'>
                <h2 style='margin: 0;'>Votre code de vérification</h2>
            </div>
            <div style='padding: 30px;'>
                <p>Bonjour $prenom,</p>
                <p>Voici votre code de vérification :</p>
                <div style='background-color: #f5f5f5; padding: 15px; text-align: center; margin: 20px 0; 
                            font-size: 24px; letter-spacing: 3px; font-weight: bold; color: #2F855A;'>
                    $new_code
                </div>
                <p>Si vous n'avez pas demandé ce code, veuillez ignorer cet email.</p>
            </div>
            <div style='background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666;'>
                © " . date('Y') . " AgriConnect Bénin - Tous droits réservés
            </div>
        </div>";
    

    $mail->AltBody = "Votre code de vérification AgriConnect est : $new_code\nValable jusqu'à " . date('H:i', strtotime($new_expiration));

    $mail->send();
    $_SESSION['authemail'] = true;
    // Redirection après succès
    header('Location: ../authemail.php');
    exit();

} catch (Exception $e) {
    error_log("Email error: " . $e->getMessage());
    header('Location: ../authform.php?error=email_failed');
    exit();
}