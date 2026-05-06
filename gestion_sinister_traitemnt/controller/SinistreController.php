<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/Sinistre.php';
require_once __DIR__ . '/../controller/FraudeService.php'; // ← ANTIFRAUD
require_once __DIR__ . '/../controller/EmailService.php';  // ← EMAIL

class SinistreController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT s.id_sinistre, s.type, s.description, s.date_declaration, s.statut, s.photo_url,
                   s.id_contrat, s.id_user,
                   CONCAT(u.prenom,' ',u.nom) AS client_nom,
                   COALESCE(c.numero_contrat, CONCAT('CNT-', s.id_contrat)) AS numero_contrat,
                   /* ANTIFRAUD : inclure le score dans la liste */
                   fa.score_global   AS fraud_score,
                   fa.niveau_risque  AS fraud_niveau,
                   fa.suggestion_ia  AS fraud_suggestion
            FROM sinistre s
            LEFT JOIN user u          ON s.id_user    = u.id_user
            LEFT JOIN contrat c       ON s.id_contrat = c.id_contrat
            LEFT JOIN fraud_analysis fa ON s.id_sinistre = fa.id_sinistre
            ORDER BY s.date_declaration DESC
        ");
        $sinistres = [];
        foreach ($stmt->fetchAll() as $row) {
            $sinistre = new Sinistre($row['id_contrat'], $row['id_user'], $row['type'], $row['description']);
            $sinistre->setIdSinistre($row['id_sinistre']);
            $sinistre->setPhotoUrl($row['photo_url']);
            $sinistre->setDateDeclaration($row['date_declaration']);
            $sinistre->setStatut($row['statut']);
            // Enrichissement antifraud
            $sinistre->fraudScore     = $row['fraud_score'];
            $sinistre->fraudNiveau    = $row['fraud_niveau'];
            $sinistre->fraudSuggestion = $row['fraud_suggestion'];
            $sinistres[] = $sinistre;
        }
        return $sinistres;
    }

    public function getByUser(int $userId): array
    {
        if (!$userId) return [];
        $stmt = $this->db->prepare("
            SELECT s.id_sinistre, s.type, s.description, s.date_declaration, s.statut, s.photo_url,
                   s.id_contrat, s.id_user,
                   COALESCE(c.numero_contrat, CONCAT('CNT-', s.id_contrat)) AS numero_contrat,
                   fa.score_global   AS fraud_score,
                   fa.niveau_risque  AS fraud_niveau,
                   fa.suggestion_ia  AS fraud_suggestion
            FROM sinistre s
            LEFT JOIN contrat c       ON s.id_contrat = c.id_contrat
            LEFT JOIN fraud_analysis fa ON s.id_sinistre = fa.id_sinistre
            WHERE s.id_user = :uid
            ORDER BY s.date_declaration DESC
        ");
        $stmt->execute([':uid' => $userId]);
        $sinistres = [];
        foreach ($stmt->fetchAll() as $row) {
            $sinistre = new Sinistre($row['id_contrat'], $row['id_user'], $row['type'], $row['description']);
            $sinistre->setIdSinistre($row['id_sinistre']);
            $sinistre->setPhotoUrl($row['photo_url']);
            $sinistre->setDateDeclaration($row['date_declaration']);
            $sinistre->setStatut($row['statut']);
            // Enrichissement antifraud
            $sinistre->fraudScore     = $row['fraud_score'];
            $sinistre->fraudNiveau    = $row['fraud_niveau'];
            $sinistre->fraudSuggestion = $row['fraud_suggestion'];
            $sinistres[] = $sinistre;
        }
        return $sinistres;
    }

    public function getById(int $id): ?Sinistre
    {
        if (!$id) return null;
        $stmt = $this->db->prepare("
            SELECT s.id_sinistre, s.type, s.description, s.date_declaration, s.statut, s.photo_url,
                   s.id_contrat, s.id_user
            FROM sinistre s
            WHERE s.id_sinistre = :id
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        $sinistre = new Sinistre($row['id_contrat'], $row['id_user'], $row['type'], $row['description']);
        $sinistre->setIdSinistre($row['id_sinistre']);
        $sinistre->setPhotoUrl($row['photo_url']);
        $sinistre->setDateDeclaration($row['date_declaration']);
        $sinistre->setStatut($row['statut']);
        return $sinistre;
    }

    public function create(array $data, int $userId, ?array $file = null): array
    {
        $idContrat   = (int)($data['id_contrat']  ?? 0);
        $type        = trim($data['type']         ?? '');
        $description = trim($data['description']  ?? '');

        if (!$userId)      return ['success' => false, 'message' => 'Utilisateur non identifie.'];
        if (!$idContrat)   return ['success' => false, 'message' => 'id_contrat manquant.'];
        if (!$type)        return ['success' => false, 'message' => 'Type de sinistre manquant.'];
        if (!$description) return ['success' => false, 'message' => 'Description manquante.'];

        // ── Upload photo ──────────────────────────────────────────────────────
        $photoUrl  = null;
        $photoPath = null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/sinistres/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed)) {
                $filename = 'sin_' . uniqid() . '.' . $ext;
                $dest     = $uploadDir . $filename;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $photoUrl  = 'uploads/sinistres/' . $filename;
                    $photoPath = $dest;
                }
            }
        }

        // ── Insertion sinistre ────────────────────────────────────────────────
        $stmt = $this->db->prepare("
            INSERT INTO sinistre (id_contrat, id_user, type, description, photo_url, date_declaration, statut)
            VALUES (:id_contrat, :id_user, :type, :description, :photo_url, CURDATE(), 'en_attente')
        ");
        $stmt->execute([
            ':id_contrat'  => $idContrat,
            ':id_user'     => $userId,
            ':type'        => $type,
            ':description' => $description,
            ':photo_url'   => $photoUrl,
        ]);

        $idSinistre = (int)$this->db->lastInsertId();

        // ── Email : déclaration reçue ─────────────────────────────────────────
        try {
            $emailService = new EmailService($this->db);
            $emailService->sendSinistreEnCours($idSinistre);
        } catch (Exception $e) {
            error_log('[SinistreController] Email send error: ' . $e->getMessage());
        }

        return [
            'success' => true,
            'message' => 'Sinistre declare avec succes.',
            'id'      => $idSinistre
        ];
    }

    public function update(int $id, array $data): array
    {
        $idContrat   = (int)($data['id_contrat']  ?? 0);
        $type        = trim($data['type']         ?? '');
        $description = trim($data['description']  ?? '');

        if (!$id || !$idContrat || !$type || !$description) {
            return ['success' => false, 'message' => 'Donnees manquantes (id, id_contrat, type, description).'];
        }

        $stmt = $this->db->prepare("
            UPDATE sinistre SET id_contrat=:id_contrat, type=:type, description=:description
            WHERE id_sinistre=:id
        ");
        $stmt->execute([
            ':id_contrat'  => $idContrat,
            ':type'        => $type,
            ':description' => $description,
            ':id'          => $id,
        ]);

        return ['success' => true, 'message' => 'Sinistre modifie.'];
    }

    public function updateStatut(int $id, string $statut): array
    {
        if (!$id || !$statut) {
            return ['success' => false, 'message' => 'Donnees manquantes.'];
        }
        $allowed = ['en_attente', 'rembourse', 'refuse'];
        if (!in_array($statut, $allowed)) {
            return ['success' => false, 'message' => 'Statut invalide.'];
        }
        $stmt = $this->db->prepare("UPDATE sinistre SET statut=:s WHERE id_sinistre=:id");
        $stmt->execute([':s' => $statut, ':id' => $id]);

        // ── Email : envoyer notification si remboursé ou refusé ───────────────
        try {
            $emailService = new EmailService($this->db);
            if ($statut === 'rembourse') {
                $emailService->sendSinistreRembourse($id);
            } elseif ($statut === 'refuse') {
                $emailService->sendSinistreRefuse($id);
            }
        } catch (Exception $e) {
            error_log('[SinistreController] Email send error on updateStatut: ' . $e->getMessage());
        }

        return ['success' => true, 'message' => 'Statut mis a jour et email envoye.'];
    }

    public function delete(int $id): array
    {
        if (!$id) {
            return ['success' => false, 'message' => 'ID manquant.'];
        }
        $stmt = $this->db->prepare("DELETE FROM sinistre WHERE id_sinistre=:id");
        $stmt->execute([':id' => $id]);
        return ['success' => true, 'message' => 'Sinistre supprime.'];
    }

    public function getStats(): array
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) AS total,
                   SUM(statut='en_attente') AS en_attente,
                   SUM(statut='rembourse')  AS rembourse,
                   SUM(statut='refuse')     AS refuse
            FROM sinistre
        ");
        return $stmt->fetch();
    }

    public function getRecentSinistres(): array
    {
        $stmt = $this->db->query("
            SELECT id_sinistre, type, date_declaration, statut 
            FROM sinistre 
            ORDER BY id_sinistre DESC 
            LIMIT 5
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadCount(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM sinistre WHERE is_read = 0");
        return (int)$stmt->fetchColumn();
    }

    public function markAllAsRead(): bool
    {
        $stmt = $this->db->prepare("UPDATE sinistre SET is_read = 1 WHERE is_read = 0");
        return $stmt->execute();
    }
}
