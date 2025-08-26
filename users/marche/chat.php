<?php
session_start();
// Sécurité : vérifier que le marché est connecté
if (!isset($_SESSION['marche_id']) || !isset($_SESSION['marche_connecte']) || $_SESSION['marche_connecte'] !== true) {
    header("Location: ../../index.php");
    exit();
}
require_once __DIR__ . '/../../data/dbconn.php';
$marche_id = $_SESSION['marche_id'];

// --- NOUVEAU : gestion ouverture automatique de conversation ---
$auto_open_conversation_id = null;
if (isset($_GET['contact_id']) && isset($_GET['type'])) {
    $contact_id = intval($_GET['contact_id']);
    $contact_type = $_GET['type'];
    // Chercher une conversation existante
    $stmt = $conn->prepare("SELECT id FROM conversations WHERE 
        ((participant1_id = ? AND participant1_type = 'marche' AND participant2_id = ? AND participant2_type = ?)
        OR (participant2_id = ? AND participant2_type = 'marche' AND participant1_id = ? AND participant1_type = ?))");
    $stmt->execute([$marche_id, $contact_id, $contact_type, $marche_id, $contact_id, $contact_type]);
    $conv = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($conv) {
        $auto_open_conversation_id = $conv['id'];
    } else {
        // Créer une nouvelle conversation
        $stmt = $conn->prepare("INSERT INTO conversations (participant1_id, participant1_type, participant2_id, participant2_type, date_creation) VALUES (?, 'marche', ?, ?, NOW())");
        $stmt->execute([$marche_id, $contact_id, $contact_type]);
        $auto_open_conversation_id = $conn->lastInsertId();
        // Redirection pour garantir l'affichage correct
        header("Location: chat.php?contact_id=$contact_id&type=$contact_type");
        exit();
    }
}
// --- FIN NOUVEAU ---
// Infos utilisateur et messages non lus
$stmt = $conn->prepare("SELECT nom FROM marches WHERE id = ?");
$stmt->execute([$marche_id]);
$marche = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM messages WHERE destinataire_id = ? AND destinataire_type = 'marche' AND lu = 0");
$stmt->execute([$marche_id]);
$unread_messages = $stmt->fetch(PDO::FETCH_ASSOC);
// Conversations triées par non lus
$stmt = $conn->prepare("SELECT c.*, 
    CASE WHEN c.participant1_id = ? THEN c.participant2_id ELSE c.participant1_id END as contact_id,
    CASE WHEN c.participant1_id = ? THEN c.participant2_type ELSE c.participant1_type END as contact_type,
    COUNT(m.id) as unread_count
FROM conversations c
LEFT JOIN messages m ON m.conversation_id = c.id 
    AND m.destinataire_id = ? 
    AND m.destinataire_type = 'marche' 
    AND m.lu = 0
WHERE (c.participant1_id = ? AND c.participant1_type = 'marche')
   OR (c.participant2_id = ? AND c.participant2_type = 'marche')
GROUP BY c.id
ORDER BY unread_count DESC, c.id DESC");
$stmt->execute([
    $marche_id, // pour CASE 1
    $marche_id, // pour CASE 2
    $marche_id, // pour LEFT JOIN
    $marche_id, // pour WHERE participant1_id
    $marche_id  // pour WHERE participant2_id
]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Patch : forcer l'ajout de la conversation nouvellement créée si absente
if ($auto_open_conversation_id) {
    $found = false;
    foreach ($conversations as $c) {
        if ($c['id'] == $auto_open_conversation_id) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $stmt = $conn->prepare("SELECT * FROM conversations WHERE id = ?");
        $stmt->execute([$auto_open_conversation_id]);
        $conv = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($conv) {
            // Ajoute les champs contact_id et contact_type comme dans la requête principale
            if ($conv['participant1_id'] == $marche_id && $conv['participant1_type'] == 'marche') {
                $conv['contact_id'] = $conv['participant2_id'];
                $conv['contact_type'] = $conv['participant2_type'];
            } else {
                $conv['contact_id'] = $conv['participant1_id'];
                $conv['contact_type'] = $conv['participant1_type'];
            }
            $conversations[] = $conv;
        }
    }
}
// Fonction pour récupérer le nom et la photo du contact
function getContactInfo($conn, $contact_id, $contact_type) {
    if ($contact_type === 'agriculteur') {
        $stmt = $conn->prepare("SELECT nom_complet, photo_profil FROM agriculteurs WHERE id = ?");
        $stmt->execute([$contact_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt = $conn->prepare("SELECT nom FROM marches WHERE id = ?");
        $stmt->execute([$contact_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $row['photo_profil'] = 'user_default_marche.jpg';
        return $row;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie - Espace Marché</title>
    <link rel="stylesheet" href="assets/css/chat.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
    .unread-badge {
        background-color: #4CAF50;
        color: white;
        border-radius: 50%;
        padding: 5px 10px;
        font-size: 12px;
        margin-left: 5px;
    }
    .conversation-info {
        display: inline-block;
        vertical-align: middle;
        margin-left: 10px;
    }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="../../assets/images/logo/Logo_back-off.png" alt="Logo AgriConnect BENIN" height="100%">
        </div>
        <ul class="nav-links">
            <li>
                <a href="chat.php" class="nav-icon active" title="Messages">
                    <i class="bi bi-envelope"></i>
                    <?php if ($unread_messages['unread_count'] > 0): ?>
                    <span class="notification-badge"><?= htmlspecialchars($unread_messages['unread_count']) ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="profile-dropdown">
                <a href="#" class="profile-link">
                    <img src="assets/images/profiles/user_default_marche.jpg" alt="Photo de profil" class="profile-pic" width="30" height="30">
                    <span><?= htmlspecialchars(explode(' ', $marche['nom'])[0]) ?></span>
                    <i class="bi bi-caret-down-fill"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="profil.php"><i class="bi bi-person"></i> Mon profil</a></li>
                    <li><a href="deconnexion.php"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <div class="chat-container">
        <div class="conversations-list">
            <h2>Conversations</h2>
            <ul>
                <?php if (empty($conversations)): ?>
                <li style="color:#888;text-align:center;padding:40px 10px;line-height:1.6;">
                    Vous n'avez encore aucune conversation.<br>
                    Contactez un agriculteur pour démarrer une discussion.
                </li>
                <?php else:
                foreach ($conversations as $conv):
                    $contact = getContactInfo($conn, $conv['contact_id'], $conv['contact_type']);
                    $photo = $contact['photo_profil'] ?? 'user_default_agriculteur.jpg';
                ?>
                <li class="conversation-item" data-conversation-id="<?= htmlspecialchars($conv['id']) ?>">
                    <img src="../agriculteur/assets/images/profiles/<?= htmlspecialchars($photo) ?>" alt="Profil" class="contact-photo">
                    <div class="conversation-info">
                        <span class="contact-name"><?= htmlspecialchars($contact['nom_complet'] ?? $contact['nom']) ?></span>
                        <?php if ($conv['unread_count'] > 0): ?>
                        <span class="unread-badge"><?= $conv['unread_count'] ?></span>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endforeach; endif; ?>
            </ul>
        </div>
        <div class="chat-window">
            <div class="chat-header">
                <span id="chat-contact-name">Sélectionnez une conversation</span>
            </div>
            <div class="chat-messages" id="chat-messages">
                <div id="no-conversation-message" style="color:#888;text-align:center;margin-top:40px;">
                    Sélectionnez une conversation à gauche pour commencer à discuter.<br>
                    Vous n'avez pas encore de messages.
                </div>
            </div>
            <form class="chat-input" id="chat-form" style="display:none;">
                <input type="text" id="message-input" placeholder="Écrivez un message..." autocomplete="off">
                <button type="submit"><i class="bi bi-send"></i></button>
            </form>
        </div>
    </div>
    <script>
    let currentConvId = null;
    let pollingInterval = null;
    // --- NOUVEAU : ouverture automatique de conversation robuste ---
    const autoOpenConvId = <?= $auto_open_conversation_id ? json_encode($auto_open_conversation_id) : 'null' ?>;
    window.addEventListener('DOMContentLoaded', function() {
        // Sélection d'une conversation
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.addEventListener('click', function() {
                const convId = this.dataset.conversationId;
                document.getElementById('chat-form').style.display = 'flex';
                currentConvId = convId;
                loadMessages(convId);
                const contactNameElem = document.getElementById('chat-contact-name');
                if (contactNameElem) {
                    contactNameElem.textContent = this.querySelector('.contact-name').textContent;
                }
                document.getElementById('chat-form').dataset.conversationId = convId;
                if (pollingInterval) clearInterval(pollingInterval);
                pollingInterval = setInterval(function() {
                    if (currentConvId) loadMessages(currentConvId);
                    updateMessagesBadge();
                }, 3000);
            });
        });
        // Ouvre automatiquement la conversation si besoin (avec délai pour robustesse)
        if (autoOpenConvId) {
            setTimeout(function() {
                const convItem = document.querySelector('.conversation-item[data-conversation-id="' + autoOpenConvId + '"]');
                console.log('autoOpenConvId:', autoOpenConvId, 'convItem:', convItem);
                if (convItem) {
                    convItem.click();
                }
            }, 150);
        }
    });
    // --- FIN NOUVEAU ---
    function loadMessages(convId) {
        if (!convId) return;
        fetch('data/charger_messages.php?conversation_id=' + convId)
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.text();
            })
            .then(html => {
                const messagesContainer = document.getElementById('chat-messages');
                messagesContainer.innerHTML = html;
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            })
            .catch(error => {
                console.error('Error loading messages:', error);
            });
    }
    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const convId = this.dataset.conversationId;
        const input = document.getElementById('message-input');
        const message = input.value.trim();
        if (!message) return;
        fetch('data/envoyer_message.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({conversation_id: convId, message: message})
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadMessages(convId);
                input.value = '';
                updateMessagesBadge();
            } else {
                alert('Erreur lors de l\'envoi du message');
            }
        });
    });
    setInterval(updateMessagesBadge, 3000);
    function updateMessagesBadge() {
        fetch('data/messages_non_lus.php')
            .then(res => res.json())
            .then(data => {
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    if (data.unread > 0) {
                        badge.textContent = data.unread;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            });
    }
    updateMessagesBadge();
    // Redirection logo
    document.querySelector('.logo').addEventListener('click', function() {
        window.location.href = "index.php";
    });
    // Curseur pointer sur le logo
    document.querySelector('.logo').style.cursor = 'pointer';
    </script>
</body>
</html> 