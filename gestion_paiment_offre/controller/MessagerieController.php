<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) { header('Location: ../view/BackOffice/connexion.html'); exit; }

include(__DIR__ . '/../config.php');
include(__DIR__ . '/../model/Message.php');

class MessagerieController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    public function index(): void
    {
        $userId = $this->getUserId();
        if (!$userId) {
            header('Location: ../view/BackOffice/connexion.html');
            exit;
        }

        $user = Message::getUserById($this->db, $userId);
        if (!$user) { die('Utilisateur introuvable'); }

        $conversations = Message::getUserConversations($this->db, $userId);
        $users = Message::getAllUsers($this->db);
        $mentions = Message::getActiveMentions($this->db, $userId);

        $convId = isset($_GET['conv']) ? (int)$_GET['conv'] : null;
        $messages = [];
        $currentConv = null;

        if ($convId) {
            $currentConv = $this->findConvInList($conversations, $convId);
            if ($currentConv) {
                $messages = Message::getMessages($this->db, $convId);
                Message::markAsRead($this->db, $convId, $userId);
                $conversations = Message::getUserConversations($this->db, $userId);
            }
        }

        require_once __DIR__ . '/../view/BackOffice/messagerie.php';
    }

    public function envoyer(): void
    {
        header('Content-Type: application/json');

        $userId = $this->getUserId();
        if (!$userId) { echo json_encode(['error' => 'Non authentifié']); return; }

        $convId = (int)($_POST['id_conversation'] ?? 0);
        $contenu = trim($_POST['contenu'] ?? '');

        if (!$convId) { echo json_encode(['error' => 'Données invalides']); return; }

        $stmt = $this->db->prepare("SELECT 1 FROM conversation_participants WHERE id_conversation = ? AND id_user = ?");
        $stmt->execute([$convId, $userId]);
        if (!$stmt->fetch()) { echo json_encode(['error' => 'Non autorisé']); return; }

        $fichierUrl = null;
        $dureeAudio = null;
        $type = 'texte';

        if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'audio/webm', 'audio/ogg', 'audio/mpeg'];
            $fileType = $_FILES['fichier']['type'];
            if (in_array($fileType, $allowedTypes)) {
                $ext = pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION);
                if (empty($ext)) {
                    $ext = match($fileType) {
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/gif' => 'gif',
                        'image/webp' => 'webp',
                        'audio/webm' => 'webm',
                        'audio/ogg' => 'ogg',
                        'audio/mpeg' => 'mp3',
                        default => 'bin',
                    };
                }
                $fileName = uniqid('msg_') . '.' . $ext;
                $uploadDir = __DIR__ . '/../uploads/messages/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                if (move_uploaded_file($_FILES['fichier']['tmp_name'], $uploadDir . $fileName)) {
                    $fichierUrl = '/projet_web1/gestion_paiment_offre/uploads/messages/' . $fileName;
                    $type = str_starts_with($fileType, 'image') ? 'image' : 'audio';
                    if ($type === 'audio') {
                        $dureeAudio = (int)($_POST['duree_audio'] ?? 0);
                    }
                }
            }
        }

        if ($type === 'texte' && $contenu === '') { echo json_encode(['error' => 'Message vide']); return; }

        if ($type === 'texte') {
            $mentions = $this->resolveMentions($contenu);
            if (!empty($mentions)) $type = 'mention';
        }

        try {
            $msgId = Message::sendMessage($this->db, $convId, $userId, $contenu, $type, $fichierUrl, $dureeAudio);
            echo json_encode(['success' => true, 'id_message' => $msgId, 'type' => $type, 'fichier_url' => $fichierUrl]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function nouveaux(): void
    {
        header('Content-Type: application/json');

        $userId = $this->getUserId();
        if (!$userId) { echo json_encode(['error' => 'Non authentifié']); return; }

        $convId = (int)($_GET['conv'] ?? 0);
        if (!$convId) { echo json_encode(['error' => 'conv manquant']); return; }

        $after = $_GET['after'] ?? null;
        if ($after) {
            $stmt = $this->db->prepare("
                SELECT m.*, u.nom, u.prenom, u.role, u.avatar
                FROM messages_admin m
                JOIN user u ON u.id_user = m.id_expediteur
                WHERE m.id_conversation = ? AND m.date_envoi > ?
                ORDER BY m.date_envoi ASC
            ");
            $stmt->execute([$convId, $after]);
        } else {
            $stmt = $this->db->prepare("
                SELECT m.*, u.nom, u.prenom, u.role, u.avatar
                FROM messages_admin m
                JOIN user u ON u.id_user = m.id_expediteur
                WHERE m.id_conversation = ?
                ORDER BY m.date_envoi DESC
                LIMIT 50
            ");
            $stmt->execute([$convId]);
        }

        echo json_encode(['messages' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    public function nouvelleConversation(): void
    {
        header('Content-Type: application/json');

        $userId = $this->getUserId();
        if (!$userId) { echo json_encode(['error' => 'Non authentifié']); return; }

        $type = $_POST['type'] ?? 'prive';

        try {
            if ($type === 'prive') {
                $destId = (int)($_POST['destinataire'] ?? 0);
                if (!$destId) { echo json_encode(['error' => 'Destinataire requis']); return; }
                $convId = Message::createPrivateConversation($this->db, $userId, $destId);
            } else {
                $nom = trim($_POST['nom'] ?? '');
                $participants = array_map('intval', $_POST['participants'] ?? []);
                if (empty($nom)) { echo json_encode(['error' => 'Nom du groupe requis']); return; }
                $convId = Message::createGroupConversation($this->db, $nom, $userId, $participants);
            }
            echo json_encode(['success' => true, 'id_conversation' => $convId]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function resoudreMention(): void
    {
        header('Content-Type: application/json');
        $mentionId = (int)($_POST['id_mention'] ?? 0);
        Message::resolveMention($this->db, $mentionId);
        echo json_encode(['success' => true]);
    }

    private function resolveMentions(string $contenu): array
    {
        $ids = [];
        if (preg_match_all('/@(\w+)/', $contenu, $matches)) {
            foreach ($matches[1] as $prenom) {
                $stmt = $this->db->prepare("SELECT id_user FROM user WHERE LOWER(prenom) LIKE ? AND statut = 'actif' AND role IN ('admin','agent') LIMIT 1");
                $stmt->execute([strtolower($prenom) . '%']);
                $u = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($u) $ids[] = (int)$u['id_user'];
            }
        }
        return $ids;
    }

    private function findConvInList(array $conversations, int $convId): ?array
    {
        foreach ($conversations as $c) {
            if ((int)$c['id_conversation'] === $convId) return $c;
        }
        return null;
    }

    private function getUserId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }
}

$controller = new MessagerieController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'envoyer':               $controller->envoyer(); break;
    case 'nouveaux':              $controller->nouveaux(); break;
    case 'nouvelle_conversation':  $controller->nouvelleConversation(); break;
    case 'resoudre_mention':      $controller->resoudreMention(); break;
    default:                      $controller->index(); break;
}
