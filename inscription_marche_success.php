<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Marché Réussie - AgriConnect Bénin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F8FAFC;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            color: #2D3748;
        }
        
        .success-container {
            max-width: 600px;
            width: 100%;
            background: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 40px;
            text-align: center;
        }
        
        .logo-container {
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
        }
        
        .logo-container img {
            height: 60px;
        }
        
        .success-icon {
            color: #38A169;
            font-size: 72px;
            margin-bottom: 20px;
            animation: bounce 1s;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-20px);}
            60% {transform: translateY(-10px);}
        }
        
        h1 {
            color: #2F855A;
            margin-bottom: 15px;
            font-size: 28px;
            font-weight: 700;
        }
        
        .success-message {
            margin-bottom: 25px;
            line-height: 1.6;
            font-size: 16px;
            color: #4A5568;
        }
        
        .email-notice {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: #2F855A;
            font-weight: 500;
        }
        
        .email-notice i {
            margin-right: 10px;
            font-size: 20px;
        }
        
        .important-note {
            background-color: #F0FFF4;
            border-left: 4px solid #38A169;
            padding: 15px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
            text-align: left;
        }
        
        .important-note i {
            color: #38A169;
            margin-right: 8px;
        }
        
        .steps {
            text-align: left;
            margin: 25px 0 30px;
            padding-left: 25px;
        }
        
        .steps li {
            margin-bottom: 12px;
            position: relative;
            list-style-type: none;
            padding-left: 30px;
        }
        
        .steps li:before {
            content: "";
            position: absolute;
            left: 0;
            top: 8px;
            width: 16px;
            height: 16px;
            background-color: #38A169;
            border-radius: 50%;
        }
        
        .steps li:nth-child(1):before { background-color: #2F855A; }
        .steps li:nth-child(2):before { background-color: #38A169; }
        .steps li:nth-child(3):before { background-color: #48BB78; }
        .steps li:nth-child(4):before { background-color: #68D391; }
        
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #2F855A;
            color: white;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 15px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            max-width: 250px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary:hover {
            background-color: #276749;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary i {
            margin-right: 10px;
        }
        
        .spam-note {
            font-size: 14px;
            color: #718096;
            margin-top: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .spam-note i {
            margin-right: 8px;
            color: #718096;
        }
        
        @media (max-width: 480px) {
            .success-container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .success-icon {
                font-size: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="logo-container">
            <img src="assets/images/logo/Logo_back-off.png" alt="AgriConnect Bénin">
        </div>
        
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1>Inscription Marché Réussie!</h1>
        
        <p class="success-message">Merci d'avoir créé votre compte Marché sur AgriConnect Bénin. Votre inscription a bien été enregistrée.</p>
        
        <div class="email-notice">
            <i class="fas fa-envelope-open-text"></i>
            <span>Un email de vérification a été envoyé à votre adresse</span>
        </div>
        
        <div class="important-note">
            <p><i class="fas fa-exclamation-circle"></i> <strong>Important :</strong> Le lien de vérification dans l'email expire après 24 heures. Si le lien expire, vous devrez vous réinscrire.</p>
        </div>
        
        <h3 style="text-align: center; margin-bottom: 15px; color: #2F855A;">Prochaines étapes :</h3>
        <ol class="steps">
            <li>Ouvrez l'email que nous venons d'envoyer</li>
            <li>Cliquez sur le lien "Activer mon compte"</li>
            <li>Votre compte Marché sera immédiatement activé</li>
            <li>Vous pourrez alors vous connecter à votre espace</li>
        </ol>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn-primary">
                <i class="fas fa-home"></i>
                Retour à l'accueil
            </a>
        </div>
        
        <p class="spam-note">
            <i class="fas fa-search"></i>
            Si vous ne trouvez pas l'email, vérifiez votre dossier spam/courriers indésirables.
        </p>
    </div>
</body>
</html>