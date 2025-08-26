<?php
session_start();
require_once __DIR__ . '/../../data/dbconn.php';

// Vérification de la session
if (!isset($_SESSION['marche_id']) && !isset($_SESSION['agriculteur_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Récupération de l'utilisateur connecté
$current_user = [
    'id' => $_SESSION['marche_id'] ?? $_SESSION['agriculteur_id'],
    'type' => isset($_SESSION['marche_id']) ? 'marche' : 'agriculteur'
];

// Récupération du contact avec qui discuter
$contact_id = $_GET['contact_id'] ?? null;
$contact_type = $_GET['contact_type'] ?? null;

if (!$contact_id || !$contact_type) {
    die("Paramètres de contact manquants");
}

// Vérification que le contact existe
try {
    if ($contact_type === 'marche') {
        $stmt = $conn->prepare("SELECT nom, email FROM marches WHERE id = ?");
    } else {
        $stmt = $conn->prepare("SELECT nom_complet as nom, email FROM agriculteurs WHERE id = ?");
    }
    $stmt->execute([$contact_id]);
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contact) {
        die("Contact introuvable");
    }
} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}

// Gestion de l'envoi de message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        try {
            // Vérifier si une conversation existe déjà
            $stmt = $conn->prepare("SELECT id FROM conversations WHERE 
                ((participant1_id = ? AND participant1_type = ? AND participant2_id = ? AND participant2_type = ?) OR 
                (participant1_id = ? AND participant1_type = ? AND participant2_id = ? AND participant2_type = ?))");
            $stmt->execute([
                $current_user['id'], $current_user['type'], $contact_id, $contact_type,
                $contact_id, $contact_type, $current_user['id'], $current_user['type']
            ]);
            $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Créer une nouvelle conversation si nécessaire
            if (!$conversation) {
                $stmt = $conn->prepare("INSERT INTO conversations 
                    (participant1_id, participant1_type, participant2_id, participant2_type) 
                    VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $current_user['id'], $current_user['type'], $contact_id, $contact_type
                ]);
                $conversation_id = $conn->lastInsertId();
            } else {
                $conversation_id = $conversation['id'];
            }
            
            // Enregistrer le message
            $stmt = $conn->prepare("INSERT INTO messages 
                (expediteur_id, expediteur_type, destinataire_id, destinataire_type, contenu, conversation_id) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $current_user['id'], $current_user['type'], 
                $contact_id, $contact_type, 
                $message,
                $conversation_id
            ]);
            
            // Réponse JSON pour AJAX
            if (isset($_POST['ajax'])) {
                echo json_encode(['status' => 'success']);
                exit();
            }
            
        } catch (PDOException $e) {
            die("Erreur d'envoi du message: " . $e->getMessage());
        }
    }
}

// Récupération des messages
try {
    $stmt = $conn->prepare("SELECT m.* FROM messages m
        JOIN conversations c ON m.conversation_id = c.id
        WHERE ((c.participant1_id = ? AND c.participant1_type = ? AND c.participant2_id = ? AND c.participant2_type = ?) OR
              (c.participant1_id = ? AND c.participant1_type = ? AND c.participant2_id = ? AND c.participant2_type = ?))
        ORDER BY m.date_envoi ASC");
    $stmt->execute([
        $current_user['id'], $current_user['type'], $contact_id, $contact_type,
        $contact_id, $contact_type, $current_user['id'], $current_user['type']
    ]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Marquer les messages comme lus
    $stmt = $conn->prepare("UPDATE messages SET lu = 1 
        WHERE destinataire_id = ? AND destinataire_type = ? 
        AND expediteur_id = ? AND expediteur_type = ?");
    $stmt->execute([
        $current_user['id'], $current_user['type'],
        $contact_id, $contact_type
    ]);
    
} catch (PDOException $e) {
    die("Erreur de récupération des messages: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation avec <?= htmlspecialchars($contact['nom']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .chat-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .chat-header {
            background-color: #2F855A;
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
        }
        .chat-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .chat-messages {
            padding: 20px;
            height: 500px;
            overflow-y: auto;
        }
        .message {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }
        .message.sent {
            align-items: flex-end;
        }
        .message.received {
            align-items: flex-start;
        }
        .message-content {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
            position: relative;
        }
        .message.sent .message-content {
            background-color: #2F855A;
            color: white;
        }
        .message.received .message-content {
            background-color: #e5e5ea;
            color: black;
        }
        .message-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .chat-input {
            display: flex;
            padding: 15px;
            background-color: #f9f9f9;
            border-top: 1px solid #eee;
        }
        .chat-input input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            outline: none;
        }
        .chat-input button {
            background-color: #2F855A;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            margin-left: 10px;
            cursor: pointer;
        }
        .typing-indicator {
            font-style: italic;
            color: #666;
            padding: 5px 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <img src="assets/images/profiles/user_default_<?= $contact_type ?>.jpg" alt="<?= htmlspecialchars($contact['nom']) ?>">
            <h3><?= htmlspecialchars($contact['nom']) ?></h3>
        </div>
        
        <div class="chat-messages" id="messages-container">
            <?php foreach ($messages as $message): ?>
                <div class="message <?= $message['expediteur_id'] == $current_user['id'] && $message['expediteur_type'] == $current_user['type'] ? 'sent' : 'received' ?>">
                    <div class="message-content">
                        <?= nl2br(htmlspecialchars($message['contenu'])) ?>
                    </div>
                    <div class="message-info">
                        <?= date('d/m/Y H:i', strtotime($message['date_envoi'])) ?>
                        <?php if ($message['expediteur_id'] == $current_user['id'] && $message['expediteur_type'] == $current_user['type']): ?>
                            <?php if ($message['lu']): ?>
                                <i class="fas fa-check-double" style="color: #2F855A;"></i>
                            <?php else: ?>
                                <i class="fas fa-check"></i>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="typing-indicator" id="typing-indicator" style="display: none;">
            <?= htmlspecialchars($contact['nom']) ?> est en train d'écrire...
        </div>
        
        <form class="chat-input" id="message-form">
            <input type="text" name="message" placeholder="Écrivez un message..." autocomplete="off" required>
            <button type="submit">Envoyer</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Auto-scroll vers le bas
        $('#messages-container').scrollTop($('#messages-container')[0].scrollHeight);
        
        // Envoi de message via AJAX
        $('#message-form').on('submit', function(e) {
            e.preventDefault();
            const messageInput = $(this).find('input[name="message"]');
            const message = messageInput.val().trim();
            
            if (message) {
                $.ajax({
                    url: window.location.href,
                    method: 'POST',
                    data: {
                        message: message,
                        ajax: true
                    },
                    success: function() {
                        // Ajouter le message localement sans recharger
                        const now = new Date();
                        const hours = now.getHours().toString().padStart(2, '0');
                        const minutes = now.getMinutes().toString().padStart(2, '0');
                        
                        $('#messages-container').append(`
                            <div class="message sent">
                                <div class="message-content">
                                    ${message.replace(/\n/g, '<br>')}
                                </div>
                                <div class="message-info">
                                    ${hours}:${minutes} <i class="fas fa-check"></i>
                                </div>
                            </div>
                        `);
                        
                        messageInput.val('');
                        $('#messages-container').scrollTop($('#messages-container')[0].scrollHeight);
                    }
                });
            }
        });
        
        // Actualisation périodique des messages
        function refreshMessages() {
            $.ajax({
                url: 'get_messages.php',
                method: 'GET',
                data: {
                    contact_id: <?= $contact_id ?>,
                    contact_type: '<?= $contact_type ?>',
                    last_message_id: <?= !empty($messages) ? end($messages)['id'] : 0 ?>
                },
                success: function(data) {
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(function(msg) {
                            const isSent = msg.expediteur_id == <?= $current_user['id'] ?> && msg.expediteur_type == '<?= $current_user['type'] ?>';
                            const hours = new Date(msg.date_envoi).getHours().toString().padStart(2, '0');
                            const minutes = new Date(msg.date_envoi).getMinutes().toString().padStart(2, '0');
                            
                            $('#messages-container').append(`
                                <div class="message ${isSent ? 'sent' : 'received'}">
                                    <div class="message-content">
                                        ${msg.contenu.replace(/\n/g, '<br>')}
                                    </div>
                                    <div class="message-info">
                                        ${hours}:${minutes}
                                        ${isSent ? (msg.lu ? '<i class="fas fa-check-double" style="color: #2F855A;"></i>' : '<i class="fas fa-check"></i>') : ''}
                                    </div>
                                </div>
                            `);
                        });
                        
                        $('#messages-container').scrollTop($('#messages-container')[0].scrollHeight);
                    }
                }
            });
        }
        
        // Toutes les 5 secondes
        setInterval(refreshMessages, 5000);
        
        // Indicateur "en train d'écrire"
        let typingTimer;
        $('input[name="message"]').on('keydown', function() {
            clearTimeout(typingTimer);
            // Envoyer une requête pour indiquer qu'on tape (non implémenté ici)
            typingTimer = setTimeout(function() {
                // Cacher l'indicateur après 2 secondes d'inactivité
                $('#typing-indicator').fadeOut();
            }, 2000);
        });
    });
    </script>
</body>
</html>