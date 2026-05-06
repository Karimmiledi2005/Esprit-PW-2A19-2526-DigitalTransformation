<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/Traitement.php';
require_once __DIR__ . '/../controller/EmailService.php'; // ← EMAIL

class TraitementController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    public function checkSinistre(int $id): ?array
    {
        if (!$id) return null;
        $stmt = $this->db->prepare("
            SELECT s.id_sinistre, s.type, s.statut,
                   CONCAT(u.prenom,' ',u.nom) AS client_nom
            FROM sinistre s
            LEFT JOIN user u ON s.id_user = u.id_user
            WHERE s.id_sinistre = :id
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT t.id_traitement, t.decision, t.montant_indemnise, t.statut, t.date_traitement, t.message_agent,
                   t.id_sinistre, t.id_user,
                   COALESCE(t.nom_agent, CONCAT(u.prenom,' ',u.nom), CONCAT('Agent #', t.id_user)) AS agent_nom,
                   s.type AS sinistre_type
            FROM traitement t
            LEFT JOIN user u ON t.id_user = u.id_user
            LEFT JOIN sinistre s ON t.id_sinistre = s.id_sinistre
            ORDER BY t.date_traitement DESC
        ");
        $traitements = [];
        foreach ($stmt->fetchAll() as $row) {
            $traitement = new Traitement($row['id_sinistre'], $row['id_user'], $row['agent_nom'], $row['decision']);
            $traitement->setIdTraitement($row['id_traitement']);
            $traitement->setMontantIndemnise($row['montant_indemnise']);
            $traitement->setStatut($row['statut']);
            $traitement->setDateTraitement($row['date_traitement']);
            $traitement->setMessageAgent($row['message_agent']);
            $traitements[] = $traitement;
        }
        return $traitements;
    }

    public function getBySinistre(int $sinistreId): array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, CONCAT(u.prenom,' ',u.nom) AS agent_nom
            FROM traitement t
            LEFT JOIN user u ON t.id_user = u.id_user
            WHERE t.id_sinistre = :id
            ORDER BY t.date_traitement ASC
        ");
        $stmt->execute([':id' => $sinistreId]);
        $traitements = [];
        foreach ($stmt->fetchAll() as $row) {
            $traitement = new Traitement($row['id_sinistre'], $row['id_user'], $row['agent_nom'], $row['decision']);
            $traitement->setIdTraitement($row['id_traitement']);
            $traitement->setMontantIndemnise($row['montant_indemnise']);
            $traitement->setStatut($row['statut']);
            $traitement->setDateTraitement($row['date_traitement']);
            $traitement->setMessageAgent($row['message_agent']);
            $traitements[] = $traitement;
        }
        return $traitements;
    }

    public function traitementExists(int $sinistreId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM traitement WHERE id_sinistre = :id");
        $stmt->execute([':id' => $sinistreId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create(array $data, int $userId): array
    {
        $idSinistre = (int)($data['id_sinistre'] ?? 0);
        $nomAgent   = trim($data['nom_agent']   ?? '');
        $decision   = trim($data['decision']    ?? '');
        $montantRaw = isset($data['montant']) ? trim($data['montant']) : '';
        $montant    = ($montantRaw !== '' && is_numeric($montantRaw)) ? (float)$montantRaw : null;
        $statut     = $data['statut'] ?? 'en_cours';
        $message    = trim($data['message_agent'] ?? '');

        if (!$idSinistre) return ['success' => false, 'message' => 'ID sinistre requis.'];
        if (!$decision)   return ['success' => false, 'message' => 'Decision requise.'];
        if (!$nomAgent)   return ['success' => false, 'message' => 'Nom de l\'agent requis.'];
        if ($montantRaw === '') return ['success' => false, 'message' => 'Montant requis.'];
        if (!$statut)     return ['success' => false, 'message' => 'Statut requis.'];
        if ($this->traitementExists($idSinistre)) {
            return ['success' => false, 'message' => "Le sinistre #$idSinistre a deja un traitement enregistre.", 'code' => 409];
        }

        $stmt = $this->db->prepare("
            INSERT INTO traitement (id_sinistre, id_user, nom_agent, decision, montant_indemnise, statut, date_traitement, message_agent)
            VALUES (:id_sinistre, :id_user, :nom_agent, :decision, :montant, :statut, CURDATE(), :message_agent)
        ");
        $stmt->execute([
            ':id_sinistre' => $idSinistre,
            ':id_user'     => $userId,
            ':nom_agent'   => $nomAgent ?: null,
            ':decision'    => $decision,
            ':montant'     => $montant,
            ':statut'      => $statut,
            ':message_agent'=> $message ?: null,
        ]);

        $id = (int)$this->db->lastInsertId();

        // Update sinistre statut
        if (in_array($statut, ['accepte', 'refuse'])) {
            $newStat = $statut === 'accepte' ? 'rembourse' : 'refuse';
            $s = $this->db->prepare("UPDATE sinistre SET statut=:s WHERE id_sinistre=:id");
            $s->execute([':s' => $newStat, ':id' => $idSinistre]);

            // ── Email : décision finale ───────────────────────────────────────
            try {
                $emailService = new EmailService($this->db);
                if ($statut === 'accepte') {
                    $emailService->sendSinistreRembourse($idSinistre, $montant);
                } else {
                    $emailService->sendSinistreRefuse($idSinistre, $message ?: null);
                }
            } catch (Exception $e) {
                error_log('[TraitementController] Email send error: ' . $e->getMessage());
            }
        }

        return ['success' => true, 'message' => 'Traitement enregistre.', 'id' => $id];
    }

    public function update(int $id, array $data): array
    {
        if (!$id) return ['success' => false, 'message' => 'ID manquant.'];
        
        $montantRaw = isset($data['montant']) ? trim($data['montant']) : '';
        $montant    = ($montantRaw !== '' && is_numeric($montantRaw)) ? (float)$montantRaw : null;
        $nomAgent   = trim($data['nom_agent'] ?? '');
        $decision   = trim($data['decision']  ?? '');
        $message    = trim($data['message_agent'] ?? '');

        if (!$nomAgent) return ['success' => false, 'message' => 'Nom de l\'agent requis.'];
        if ($decision === '')   return ['success' => false, 'message' => 'Decision requise.'];
        if ($montantRaw === '') return ['success' => false, 'message' => 'Montant requis.'];
        if (($data['statut'] ?? '') === '') return ['success' => false, 'message' => 'Statut requis.'];

        $stmt = $this->db->prepare("
            UPDATE traitement SET nom_agent=:nom_agent, decision=:decision, montant_indemnise=:montant, statut=:statut, message_agent=:message_agent
            WHERE id_traitement=:id
        ");
        $stmt->execute([
            ':nom_agent'     => trim($data['nom_agent'] ?? ''),
            ':decision'      => trim($data['decision']  ?? ''),
            ':montant'       => $montant,
            ':statut'        => $data['statut'] ?? 'en_cours',
            ':message_agent' => $message ?: null,
            ':id'            => $id,
        ]);

        // ── Email si décision finale prise lors de la mise à jour ─────────────
        $newStatut = $data['statut'] ?? 'en_cours';
        if (in_array($newStatut, ['accepte', 'refuse'])) {
            // Récupérer l'id_sinistre lié à ce traitement
            $stmtSin = $this->db->prepare("SELECT id_sinistre FROM traitement WHERE id_traitement=:id");
            $stmtSin->execute([':id' => $id]);
            $idSinistre = (int)$stmtSin->fetchColumn();

            if ($idSinistre) {
                // Mettre à jour le statut du sinistre
                $newSinistreStat = $newStatut === 'accepte' ? 'rembourse' : 'refuse';
                $sStmt = $this->db->prepare("UPDATE sinistre SET statut=:s WHERE id_sinistre=:id");
                $sStmt->execute([':s' => $newSinistreStat, ':id' => $idSinistre]);

                // Envoyer email
                try {
                    $emailService = new EmailService($this->db);
                    if ($newStatut === 'accepte') {
                        $emailService->sendSinistreRembourse($idSinistre, $montant);
                    } else {
                        $emailService->sendSinistreRefuse($idSinistre, $message ?: null);
                    }
                } catch (Exception $e) {
                    error_log('[TraitementController] Email update error: ' . $e->getMessage());
                }
            }
        }

        return ['success' => true, 'message' => 'Traitement mis a jour.'];
    }

    public function delete(int $id): array
    {
        if (!$id) return ['success' => false, 'message' => 'ID manquant.'];
        
        // 1. Récupérer l'id_sinistre associé avant suppression
        $stmtGet = $this->db->prepare("SELECT id_sinistre FROM traitement WHERE id_traitement=:id");
        $stmtGet->execute([':id' => $id]);
        $idSinistre = $stmtGet->fetchColumn();

        // 2. Supprimer le traitement
        $stmt = $this->db->prepare("DELETE FROM traitement WHERE id_traitement=:id");
        $stmt->execute([':id' => $id]);

        // 3. Supprimer le sinistre associé si trouvé
        if ($idSinistre) {
            $stmtS = $this->db->prepare("DELETE FROM sinistre WHERE id_sinistre=:id");
            $stmtS->execute([':id' => $idSinistre]);
        }

        return ['success' => true, 'message' => 'Traitement et sinistre supprimés.'];
    }

    public function getStats(): array
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) AS total,
                   COUNT(DISTINCT id_sinistre) AS nb_sinistres,
                   SUM(statut='en_cours') AS en_cours,
                   SUM(statut='accepte') AS accepte,
                   SUM(statut='refuse') AS refuse
            FROM traitement
        ");
        return $stmt->fetch();
    }
}
