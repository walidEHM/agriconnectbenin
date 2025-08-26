<?php
require_once __DIR__ . '/data/dbconn.php';
date_default_timezone_set('Africa/Porto-Novo');

if (isset($_GET['token']) && isset($_GET['id'])) {
    $token = $_GET['token'];
    $id = $_GET['id'];
    
    try {
        $sql = "SELECT id, date_inscription FROM agriculteurs 
                WHERE id = :id 
                AND token_verification = :token 
                AND compte_active = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $agriculteurs = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Convertir les dates en timestamps (secondes)
            $date_inscription = strtotime($agriculteurs['date_inscription']);
            $now = time(); // Timestamp actuel
            
            // Calculer la différence en secondes
            $difference_seconds = $now - $date_inscription;
            
            // 24 heures = 86400 secondes
            if ($difference_seconds < 86400) {
                // Activer le compte (valide)
                $stmt = $conn->prepare("UPDATE agriculteurs
                                      SET compte_active = 1, 
                                          token_verification = NULL
                                      WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Activation réussie - AgriConnect Bénin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --vert-principal: #2F855A;
            --vert-secondaire: #38A169;
            --vert-clair: #F0FFF4;
            --gris-fonce: #2D3748;
            --gris-moyen: #718096;
            --gris-clair: #E2E8F0;
            --blanc: #FFFFFF;
            --rouge: #E53E3E;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--vert-clair);
            color: var(--gris-fonce);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .container {
            background-color: var(--blanc);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        
        .logo-container {
            margin-bottom: 30px;
        }
        
        .logo-container img {
            height: 60px;
        }
        
        .icon {
            font-size: 72px;
            margin-bottom: 20px;
            color: var(--vert-secondaire);
            animation: bounce 1s;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-20px); }
            60% { transform: translateY(-10px); }
        }
        
        h1 {
            color: var(--vert-principal);
            margin-bottom: 15px;
            font-size: 28px;
            font-weight: 700;
        }
        
        p {
            margin-bottom: 25px;
            font-size: 16px;
            line-height: 1.6;
            color: var(--gris-moyen);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: var(--vert-principal);
            color: var(--blanc);
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(47, 133, 90, 0.3);
        }
        
        .btn:hover {
            background-color: #276749;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(47, 133, 90, 0.4);
        }
        
        .btn i {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="assets/images/logo/Logo_back-off.png" alt="AgriConnect Bénin">
        </div>
        
        <i class="fas fa-check-circle icon"></i>
        <h1>Compte agriculteurs Activé!</h1>
        <p>Votre compte agriculteurs a été activé avec succès. Vous pouvez maintenant accéder à votre espace et commencer à connecter avec les producteurs agricoles du Bénin.</p>
        <a href='identification_agriculteur.php#showLoginForm' class='btn'>
            <i class="fas fa-sign-in-alt"></i> Se connecter
        </a>
    </div>
</body>
</html>
<?php
            } else {
                // Lien expiré
?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Lien expiré - AgriConnect Bénin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --vert-principal: #2F855A;
            --orange: #ED8936;
            --rouge: #E53E3E;
            --gris-fonce: #2D3748;
            --gris-moyen: #718096;
            --blanc: #FFFFFF;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F8FAFC;
            color: var(--gris-fonce);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .container {
            background-color: var(--blanc);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        
        .icon {
            font-size: 72px;
            margin-bottom: 20px;
            color: var(--rouge);
        }
        
        h1 {
            color: var(--gris-fonce);
            margin-bottom: 15px;
            font-size: 28px;
            font-weight: 700;
        }
        
        p {
            margin-bottom: 25px;
            font-size: 16px;
            line-height: 1.6;
            color: var(--gris-moyen);
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 16px;
        }
        
        .btn-primary {
            background-color: var(--vert-principal);
            color: var(--blanc);
            box-shadow: 0 4px 15px rgba(47, 133, 90, 0.3);
        }
        
        .btn-primary:hover {
            background-color: #276749;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: var(--orange);
            color: var(--blanc);
            box-shadow: 0 4px 15px rgba(237, 137, 54, 0.3);
        }
        
        .btn-secondary:hover {
            background-color: #DD6B20;
            transform: translateY(-2px);
        }
        
        .btn i {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <i class="fas fa-exclamation-triangle icon"></i>
        <h1>Lien expiré</h1>
        <p>Le lien d'activation a dépassé la durée de validité de 24 heures. Pour des raisons de sécurité, veuillez vous inscrire à nouveau.</p>
        
        <div class="btn-group">
            <a href='identification_agriculteurs.php#showRegisterForm' class='btn btn-primary'>
                <i class="fas fa-user-plus"></i> Nouvelle inscription
            </a>
            <a href='index.php' class='btn btn-secondary'>
                <i class="fas fa-home"></i> Accueil
            </a>
        </div>
    </div>
</body>
</html>
<?php
                // Supprimer l'entrée expirée
                $stmt = $conn->prepare("DELETE FROM agriculteurs WHERE id = :id AND compte_active = 0");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
            }
        } else {
            // Token invalide ou compte déjà activé
?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Lien invalide - AgriConnect Bénin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --vert-principal: #2F855A;
            --orange: #ED8936;
            --rouge: #E53E3E;
            --gris-fonce: #2D3748;
            --gris-moyen: #718096;
            --blanc: #FFFFFF;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F8FAFC;
            color: var(--gris-fonce);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .container {
            background-color: var(--blanc);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        
        .icon {
            font-size: 72px;
            margin-bottom: 20px;
            color: var(--gris-moyen);
        }
        
        h1 {
            color: var(--gris-fonce);
            margin-bottom: 15px;
            font-size: 28px;
            font-weight: 700;
        }
        
        p {
            margin-bottom: 25px;
            font-size: 16px;
            line-height: 1.6;
            color: var(--gris-moyen);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: var(--vert-principal);
            color: var(--blanc);
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(47, 133, 90, 0.3);
        }
        
        .btn:hover {
            background-color: #276749;
            transform: translateY(-2px);
        }
        
        .btn i {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <i class="fas fa-unlink icon"></i>
        <h1>Lien invalide</h1>
        <p>Ce lien d'activation est invalide ou a déjà été utilisé. Si vous pensez qu'il s'agit d'une erreur, veuillez contacter notre support.</p>
        <a href='index.php' class='btn'>
            <i class="fas fa-home"></i> Retour à l'accueil
        </a>
    </div>
</body>
</html>
<?php
        }
    } catch (PDOException $e) {
        die("Erreur de base de données: " . $e->getMessage());
    }
} else {
    header('Location: index.php');
    exit();
}