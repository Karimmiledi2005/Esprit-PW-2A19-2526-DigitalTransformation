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

        // Marquer la réclamation comme résolue
        $this->db->prepare(
            "UPDATE reclamation SET statut = 'closed' WHERE id = ?"
        )->execute([$r->getReclamationId()]);
    }

    // ── READ ALL (avec JOIN réclamation) ─────────────────────────────────────
    public function listReponses()
    {
        return $this->db->query(
            "SELECT rep.*,
                    r.objet       AS rec_objet,
                    r.type        AS rec_type,
                    r.ref_contrat AS rec_ref_contrat,
                    r.priorite    AS rec_priorite,
                    r.email       AS rec_email
             FROM reponse rep
             INNER JOIN reclamation r ON r.id = rep.reclamation_id
             ORDER BY rep.id_re DESC"
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
        // Remettre la réclamation en attente avant de supprimer
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

    // ── LIST ALL RECLAMATIONS + REPONSE (Back Office) ────────────────────────    
    public function listAllReclamations()
    {
        $sql = "SELECT
                    r.id,
                    r.objet,
                    r.type,
                    r.ref_contrat,
                    r.priorite,
                    r.statut,
                    r.date_depot,
                    r.rec_ref,
                    r.email,
                    r.description,
                    rep.id_re        AS rep_id,
                    rep.contenu      AS reponse_contenu,
                    rep.date_reponse AS rep_date,
                    rep.statut       AS rep_statut
                FROM reclamation r
                LEFT JOIN reponse rep ON rep.reclamation_id = r.id
                ORDER BY r.date_depot DESC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
