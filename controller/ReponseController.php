<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/ReponseModel.php';

class ReponseController
{
    private $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    // ── CREATE ───────────────────────────────────────────────────────────────
    public function addReponse(Reponse $r)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO reponse (date_reponse, contenu, statut, reclamation_id)
             VALUES (?, ?, 'envoyee', ?)"
        );
        $stmt->execute([date('Y-m-d'), $r->getContenu(), $r->getReclamationId()]);

        $this->db->prepare(
            "UPDATE reclamation SET statut = 'closed' WHERE id = ?"
        )->execute([$r->getReclamationId()]);
    }

    // ── READ ALL ─────────────────────────────────────────────────────────────
    public function listReponses()
    {
        return $this->db->query(
            "SELECT rep.*,
                    r.objet       AS rec_objet,
                    r.type        AS rec_type,
                    r.ref_contrat AS rec_ref_contrat,
                    r.priorite    AS rec_priorite
             FROM reponse rep
             INNER JOIN reclamation r ON r.id = rep.reclamation_id
             ORDER BY r.objet ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── READ ONE ─────────────────────────────────────────────────────────────
    public function showReponse($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM reponse WHERE id_re = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ── UPDATE ───────────────────────────────────────────────────────────────
    public function updateReponse($id, $contenu)
    {
        $this->db->prepare(
            "UPDATE reponse SET contenu = ? WHERE id_re = ?"
        )->execute([$contenu, (int)$id]);
    }

    // ── DELETE ───────────────────────────────────────────────────────────────
    public function deleteReponse($id)
    {
        $this->db->prepare(
            "UPDATE reclamation SET statut = 'open'
             WHERE id = (SELECT reclamation_id FROM reponse WHERE id_re = ?)"
        )->execute([(int)$id]);

        $this->db->prepare(
            "DELETE FROM reponse WHERE id_re = ?"
        )->execute([(int)$id]);
    }

    // ── REJETER ──────────────────────────────────────────────────────────────
    public function rejeterReclamation($reclamation_id, $motif)
    {
        $this->db->prepare(
            "INSERT INTO reponse (date_reponse, contenu, statut, reclamation_id)
             VALUES (?, ?, 'rejetee', ?)"
        )->execute([date('Y-m-d'), $motif, (int)$reclamation_id]);

        $this->db->prepare(
            "UPDATE reclamation SET statut = 'rejected' WHERE id = ?"
        )->execute([(int)$reclamation_id]);
    }

    // ── GET REPONSE BY RECLAMATION ────────────────────────────────────────────
    public function getReponseByReclamation($reclamation_id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM reponse WHERE reclamation_id = ? LIMIT 1"
        );
        $stmt->execute([(int)$reclamation_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ── STATS PAR TYPE ───────────────────────────────────────────────────────
    public function getStatsByType(): array
    {
        return $this->db->query(
            "SELECT r.type, COUNT(*) AS total
             FROM reclamation r
             GROUP BY r.type
             ORDER BY total DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── LIST ALL RECLAMATIONS + REPONSE ──────────────────────────────────────
    public function listAllReclamations()
    {
        return $this->db->query(
            "SELECT
                r.id, r.objet, r.type, r.ref_contrat, r.priorite,
                r.statut, r.date_depot, r.rec_ref, r.description, r.email,
                rep.id_re        AS rep_id,
                rep.contenu      AS reponse_contenu,
                rep.date_reponse AS rep_date,
                rep.statut       AS rep_statut
             FROM reclamation r
             LEFT JOIN reponse rep ON rep.reclamation_id = r.id
             ORDER BY r.objet ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── SEARCH BY OBJET ───────────────────────────────────────────────────────
    public function searchAllReclamationsByObjet(string $objet): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                r.id, r.objet, r.type, r.ref_contrat, r.priorite,
                r.statut, r.date_depot, r.rec_ref, r.description, r.email,
                rep.id_re        AS rep_id,
                rep.contenu      AS reponse_contenu,
                rep.date_reponse AS rep_date,
                rep.statut       AS rep_statut
             FROM reclamation r
             LEFT JOIN reponse rep ON rep.reclamation_id = r.id
             WHERE r.objet LIKE :objet
             ORDER BY r.objet ASC"
        );
        $stmt->bindValue(':objet', '%' . $objet . '%', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================================================================
    //  MÉTHODE PRINCIPALE : Ajouter une réponse + envoyer l'email
    //  Même approche que GaiaLumen → updateStatusWithEmailNotification()
    // =========================================================================
    public function addReponseAvecEmail(int $reclamation_id, string $contenu, string $emailClient = ''): array
    {
        // 1. Récupérer les données de la réclamation
        $stmt = $this->db->prepare("SELECT * FROM reclamation WHERE id = ?");
        $stmt->execute([$reclamation_id]);
        $rec = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rec) {
            return ['success' => false, 'email_sent' => false, 'message' => 'Réclamation introuvable.'];
        }

        // 2. Enregistrer la réponse en base de données
        $reponse = new Reponse(null, date('Y-m-d'), $contenu, 'envoyee', $reclamation_id);
        $this->addReponse($reponse);

        // 3. Envoyer l'email au client
        require_once __DIR__ . '/../services/EmailService.php';

        $email     = $emailClient ?: ($rec['email'] ?? '');
        $objet     = $rec['objet'] ?? 'Réclamation';
        $emailSent = false;

        if (!empty(trim($email))) {
            $emailSent = EmailService::envoyerNotificationReponse($email, $objet, $contenu, 'reponse');
        }

        return [
            'success'    => true,
            'email_sent' => $emailSent,
            'email'      => $email,
            'message'    => $emailSent
                ? "Réponse enregistrée et email envoyé à {$email}."
                : "Réponse enregistrée. Aucun email (adresse manquante ou erreur SMTP).",
        ];
    }

    // =========================================================================
    //  MÉTHODE PRINCIPALE : Rejeter une réclamation + envoyer l'email
    //  Même approche que GaiaLumen → updateStatusWithEmailNotification()
    // =========================================================================
    public function rejeterAvecEmail(int $reclamation_id, string $motif, string $emailClient = ''): array
    {
        // 1. Récupérer les données de la réclamation
        $stmt = $this->db->prepare("SELECT * FROM reclamation WHERE id = ?");
        $stmt->execute([$reclamation_id]);
        $rec = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rec) {
            return ['success' => false, 'email_sent' => false, 'message' => 'Réclamation introuvable.'];
        }

        // 2. Enregistrer le rejet en base de données
        $this->rejeterReclamation($reclamation_id, $motif);

        // 3. Envoyer l'email au client
        require_once __DIR__ . '/../services/EmailService.php';

        $email     = $emailClient ?: ($rec['email'] ?? '');
        $objet     = $rec['objet'] ?? 'Réclamation';
        $emailSent = false;

        if (!empty(trim($email))) {
            $emailSent = EmailService::envoyerNotificationReponse($email, $objet, $motif, 'rejet');
        }

        return [
            'success'    => true,
            'email_sent' => $emailSent,
            'email'      => $email,
            'message'    => $emailSent
                ? "Réclamation rejetée et email envoyé à {$email}."
                : "Réclamation rejetée. Aucun email (adresse manquante ou erreur SMTP).",
        ];
    }
}
?>
