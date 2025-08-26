<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription réussie - AgriConnect Bénin</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            max-width: 600px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        
        .logo_succes {
            max-width: 180px;
            margin-bottom: 25px;
        }
        
        h1 {
            color: #2e7d32;
            margin-bottom: 20px;
            font-size: 28px;
            text-align: center;
        }
        
        p {
            margin-bottom: 20px;
            line-height: 1.6;
            font-size: 16px;
            text-align: center;
        }
        
        .icon-success {
            color: #2e7d32;
            font-size: 60px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .button {
            display: inline-block;
            background-color: #2e7d32;
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
            text-align: center;
        }
        
        .button:hover {
            background-color: #1b5e20;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .important-note {
            background-color: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 25px 0;
            border-radius: 0 4px 4px 0;
        }
        
        .important-note i {
            color: #ff9800;
            margin-right: 8px;
        }
        
        .contact-link {
            color: #2e7d32;
            font-weight: 600;
            text-decoration: none;
        }
        
        .contact-link:hover {
            text-decoration: underline;
        }
        
        .steps {
            text-align: left;
            margin: 20px 0;
            padding-left: 20px;
        }
        
        .steps li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-success">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1>Inscription réussie!</h1>
        
        <p>
            <i class="fas fa-envelope" style="margin-right: 8px; color: #2e7d32;"></i>
            Un email de vérification a été envoyé à votre adresse email.
        </p>
        
        <div class="important-note">
            <p><i class="fas fa-exclamation-circle"></i> <strong>Important :</strong> Le lien de vérification dans l'email expire après 15 minutes. Si le lien expire, vous devrez vous réinscrire.</p>
        </div>
        
        <h3 style="text-align: center; margin-bottom: 15px;">Prochaines étapes :</h3>
        <ol class="steps">
            <li>Ouvrez l'email que nous venons d'envoyer</li>
            <li>Cliquez sur le lien de vérification</li>
            <li>Votre compte sera immédiatement activé</li>
            <li>Vous pourrez alors vous connecter</li>
        </ol>
        
        <p style="font-size: 14px; color: #666;">
            <i class="fas fa-question-circle" style="margin-right: 5px;"></i>
            Si vous ne trouvez pas l'email, vérifiez votre dossier spam .
        </p>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="button">
                <i class="fas fa-home" style="margin-right: 8px;"></i>
                Retour à l'accueil
            </a>
        </div>
    </div>
</body>
</html>