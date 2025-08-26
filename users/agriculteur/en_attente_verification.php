<?php
session_start();

// Vérification de session
if (empty($_SESSION['agriculteur_id']) || empty($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    header("Location: ../../index.php");
    exit();
}

require_once __DIR__ . '/../../data/dbconn.php';

try {
    $stmt = $conn->prepare("SELECT nom_complet, compte_verifie FROM agriculteurs WHERE id = ?");
    $stmt->execute([$_SESSION['agriculteur_id']]);
    $compte = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($compte['compte_verifie'] == 1) {
        header("Location: /index.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Erreur BDD: " . $e->getMessage());
    echo "Une erreur est survenue lors de la connexion à la base de données. Veuillez réessayer plus tard.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification en cours - AgriConnect BENIN</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #00AB55;
            --primary-dark: #007B55;
            --primary-light: #E5F7EE;
            --secondary: #FFAB00;
            --accent: #00AB55;
            --error: #FF5630;
            --success: #36B37E;
            --text: #212B36;
            --text-light: #637381;
            --background: #F9FAFB;
            --card-bg: #FFFFFF;
            --warning-bg: #FFF5CC;
            --info-bg: #E6F3FF;
            --border-radius: 12px;
            --box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .container {
            max-width: 700px;
            margin: 0 auto;
            padding: 2rem;
            flex: 1;
            display: flex;
            align-items: center;
        }
        
        .verification-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 3.5rem;
            box-shadow: var(--box-shadow);
            width: 100%;
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .verification-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--primary));
        }
        
        .status-container {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .status-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-light);
            border-radius: 50%;
            color: var(--primary);
            font-size: 3.5rem;
            position: relative;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        h1 {
            font-size: 2.25rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--text);
            line-height: 1.2;
        }
        
        .user-greeting {
            font-size: 1.1rem;
            color: var(--text-light);
            margin-bottom: 2.5rem;
        }
        
        .alert-warning {
            background-color: var(--warning-bg);
            color: #7A4F01;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2.5rem;
            border-left: 4px solid var(--secondary);
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .alert-warning i {
            color: var(--secondary);
            font-size: 1.25rem;
            margin-top: 2px;
        }
        
        .progress-container {
            margin: 2.5rem 0;
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        
        .progress-label {
            font-size: 0.95rem;
            color: var(--text-light);
            font-weight: 500;
        }
        
        .progress-percent {
            font-weight: 600;
            color: var(--primary);
        }
        
        .progress-bar-container {
            height: 10px;
            background-color: #E5E8EB;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            width: 75%;
            border-radius: 5px;
            position: relative;
            animation: progressAnimation 2s ease-in-out infinite;
        }
        
        @keyframes progressAnimation {
            0% { width: 70%; }
            50% { width: 80%; }
            100% { width: 70%; }
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .section-title i {
            color: var(--primary);
        }
        
        .document-list {
            margin-bottom: 2.5rem;
        }
        
        .document-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 0;
            border-bottom: 1px solid rgba(145, 158, 171, 0.2);
        }
        
        .document-item:last-child {
            border-bottom: none;
        }
        
        .document-name {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .document-name i {
            color: var(--text-light);
            font-size: 1.25rem;
        }
        
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 16px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .badge-success {
            background-color: rgba(54, 179, 126, 0.16);
            color: var(--success);
        }
        
        .info-section {
            background-color: var(--info-bg);
            padding: 1.75rem;
            border-radius: var(--border-radius);
            margin-top: 2rem;
        }
        
        .info-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }
        
        .info-title i {
            color: var(--accent);
            font-size: 1.5rem;
        }
        
        .info-list {
            padding-left: 1.5rem;
        }
        
        .info-list li {
            margin-bottom: 0.75rem;
            position: relative;
        }
        
        .info-list li::marker {
            color: var(--accent);
        }
        
        .actions {
            display: flex;
            justify-content: center;
            margin-top: 3rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
        }
        
        .btn-outline {
            border: 1px solid var(--primary);
            color: var(--primary);
            background-color: transparent;
        }
        
        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 171, 85, 0.2);
        }
        
        footer {
            text-align: center;
            padding: 1.5rem;
            color: var(--text-light);
            font-size: 0.9rem;
            margin-top: auto;
        }
        
        /* Animation des éléments */
        .animate-item {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s forwards;
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 1.5rem;
            }
            
            .verification-card {
                padding: 2rem 1.5rem;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            .status-icon {
                width: 80px;
                height: 80px;
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verification-card">
            <div class="status-container">
                <div class="status-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                
                <h1>Vérification en cours</h1>
                
                <p class="user-greeting">Bonjour <strong><?php echo htmlspecialchars($compte['nom_complet'] ?? ''); ?></strong>, votre compte est en cours de vérification</p>
                
                <div class="alert-warning animate-item" style="animation-delay: 0.1s">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <p>Nous avons bien reçu votre dossier complet et il est actuellement en cours d'examen par nos services.</p>
                        <p class="mb-0">Le délai de traitement est généralement de 24 à 48 heures.</p>
                    </div>
                </div>
            </div>
            
            <div class="progress-container animate-item" style="animation-delay: 0.2s">
                <div class="progress-header">
                    <span class="progress-label">Progression de la vérification</span>
                    <span class="progress-percent">en cour</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar"></div>
                </div>
            </div>
            
            <div class="document-section animate-item" style="animation-delay: 0.3s">
                <h3 class="section-title">
                    <i class="fas fa-file-alt"></i>
                    <span>Vos documents</span>
                </h3>
                <div class="document-list">
                    <div class="document-item">
                        <div class="document-name">
                            <i class="fas fa-id-card"></i>
                            <span>Pièce d'identité</span>
                        </div>
                        <span class="badge badge-success">
                            <i class="fas fa-check-circle"></i> verification en attente
                        </span>
                    </div>
                    <div class="document-item">
                        <div class="document-name">
                            <i class="fas fa-certificate"></i>
                            <span>Certificat de culture</span>
                        </div>
                        <span class="badge badge-success">
                            <i class="fas fa-check-circle"></i> verification en attente
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="info-section animate-item" style="animation-delay: 0.4s">
                <div class="info-title">
                    <i class="fas fa-info-circle"></i>
                    <h3>Procédure de vérification</h3>
                </div>
                <ul class="info-list">
                    <li>Analyse manuelle par nos agents agréés</li>
                    <li>Vérification de l'authenticité des documents</li>
                    <li>Validation finale par notre comité</li>
                </ul>
            </div>
            
            <div class="actions animate-item" style="animation-delay: 0.5s">
                <a href="deconnexion.php" class="btn btn-outline">
                    <i class="fas fa-sign-out-alt"></i> Se déconnecter
                </a>
            </div>
        </div>
    </div>

    <footer>
        <p>AgriConnect BENIN &copy; <?php echo date('Y'); ?> - Tous droits réservés</p>
    </footer>

    <script>
        // Actualisation automatique toutes les 5 minutes
        setTimeout(function(){
            window.location.reload();
        }, 300000);
        
        // Animation des éléments
        document.addEventListener('DOMContentLoaded', function() {
            const animateItems = document.querySelectorAll('.animate-item');
            animateItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>