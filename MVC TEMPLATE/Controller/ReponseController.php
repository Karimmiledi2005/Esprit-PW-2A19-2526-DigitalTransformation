<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/ReponseModel.php';

class ReponseController
{
    private $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    public function addReponse(Reponse $r)
    {
        $sql = "INSERT INTO Reponse
                (contenu, statut, reclamation_id)
                VALUES (?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $r->getContenu(),
            $r->getStatut(),
            $r->getReclamationId()
        ]);

        // Mettre à jour le statut de la réclamation à "closed" après réponse
        $stmtUp = $this->db->prepare("UPDATE reclamations SET statut = 'closed' WHERE id = ?");
        $stmtUp->execute([$r->getReclamationId()]);
    }

    public function listReponses()
    {
        $sql = "SELECT * FROM Reponse ORDER BY id_re DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteReponse($id)
    {
        $stmt = $this->db->prepare("DELETE FROM Reponse WHERE id_re = ?");
        $stmt->execute([$id]);
    }

    public function showReponse($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM Reponse WHERE id_re = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getReponseByReclamation($reclamation_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM Reponse WHERE reclamation_id = ? LIMIT 1");
        $stmt->execute([$reclamation_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Colonnes explicites pour eviter les conflits entre r.* et rep.*
    public function updateReponse($id, $contenu)
    {
        $stmt = $this->db->prepare("UPDATE Reponse SET contenu = ? WHERE id_re = ?");
        $stmt->execute([$contenu, $id]);
    }

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
                    rep.statut       AS rep_statut
                FROM reclamations r
                LEFT JOIN Reponse rep ON r.id = rep.reclamation_id
                ORDER BY r.date_depot DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
