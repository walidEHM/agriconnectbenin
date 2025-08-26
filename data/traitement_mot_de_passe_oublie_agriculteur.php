<?php
require_once __DIR__ . '/../src/vendor/autoload.php';
require_once __DIR__ . '/../src/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../src/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../src/vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/dbconn.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');  // Important pour indiquer que la réponse est JSON

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email requis']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM agriculteurs WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() <= 0) {
        // Pour sécurité, on répond succès même si email inexistant
        echo json_encode(['success' => false, 'message' => 'Si votre email existe dans notre système, vous recevrez un code de réinitialisation.']);
        exit();
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user['compte_active'] != 1) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Votre compte n\'est pas encore activé.']);
        exit();
    }

    $code = sprintf("%06d", mt_rand(1, 999999));
    $code_expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    $update_stmt = $conn->prepare("UPDATE agriculteurs SET token_verification = :code, expiration_token = :expires WHERE id = :id");
    $update_stmt->bindParam(':code', $code);
    $update_stmt->bindParam(':expires', $code_expires);
    $update_stmt->bindParam(':id', $user['id']);
    $update_stmt->execute();

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'elwalid2008@gmail.com';          // Ton email SMTP
            $mail->Password = 'nwki cznq bqij mkou';            // Ton mot de passe SMTP (app spécifique)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Configuration pour emails uniques
            $mail->MessageID = md5(uniqid()) . '@agriconnectbenin.com';
            $mail->addCustomHeader('X-Entity-Ref-ID', uniqid());
            $mail->clearReplyTos();
            $mail->addReplyTo('no-reply-' . uniqid() . '@agriconnectbenin.com', 'No Reply');

            $mail->setFrom('elwalid2008@gmail.com', 'AgriConnect Bénin');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "=?UTF-8?B?" . base64_encode('Code de réinitialisation AgriConnect - ' . date('Y-m-d H:i:s')) . "?=";
            $mail->Body = "
                <div style='font-family: poppins; background-color: #f9f9f9; padding: 20px;'>
                    <div style='max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.08);'>
                        <!-- Header -->
                        <div style='background-color: #2F855A; padding: 24px; text-align: center; color: #ffffff;'>
                            <h1 style='margin: 0; font-size: 26px;'>Code de vérification</h1>
                        </div>
                        <!-- Body -->
                        <div style='padding: 30px; color: #333333;'>
                            <p style='font-size: 16px;'>Bonjour <strong>".$user['prenom']."</strong>,</p>
                            <p style='font-size: 16px; line-height: 1.6;'>Voici votre code de vérification pour réinitialiser votre mot de passe :</p>
                            <div style='text-align: center; margin: 30px 0;'>
                                <div style='display: inline-block; background-color: #F0FFF4; border: 2px dashed #2F855A; padding: 15px 30px; border-radius: 8px; font-size: 24px; font-weight: bold; letter-spacing: 2px; color: #2F855A;'>
                                    $code
                                </div>
                            </div>
                            <p style='font-size: 14px; color: #555;'>
                                Ce code est valable pendant 15 minutes.<br>
                                Si vous n'avez pas fait cette demande, veuillez ignorer cet email.
                            </p>
                            <p style='margin-top: 40px; font-size: 14px;'>Cordialement,<br><strong>L'équipe AgriConnect Bénin</strong></p>
                        </div>
                        <!-- Footer -->
                        <div style='background-color: #f0f0f0; padding: 18px; text-align: center; font-size: 12px; color: #777;'>
                            &copy; ".date('Y')." AgriConnect Bénin &middot; Tous droits réservés
                        </div>
                    </div>
                </div>
            ";

            $mail->AltBody = "Votre code de réinitialisation AgriConnect est : $code\n\nCe code est valable pendant 15 minutes.";

            $mail->send();

        echo json_encode(['success' => true, 'email' => $email, 'message' => 'Un code de réinitialisation a été envoyé à votre adresse email.']);
        exit();

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi de l\'email.']);
        exit();
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur technique.']);
    exit();
}
?>