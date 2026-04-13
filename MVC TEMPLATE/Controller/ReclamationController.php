<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/ReclamationModel.php';

class ReclamationController
{
    private $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    public function addReclamation(Reclamation $r)
    {
        $sql = "INSERT INTO reclamations
                (objet, type, ref_contrat, priorite, statut, date_depot, rec_ref, email, description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $r->getObjet(),
            $r->getType(),
            $r->getRefContrat(),
            $r->getPriorite(),
            $r->getStatut(),
            $r->getDateDepot()->format('Y-m-d H:i:s'),
            $r->getRecRef(),
            $r->getEmail(),
            $r->getDescription()
        ]);
    }

    public function listReclamations()
    {
        $sql = "SELECT * FROM reclamations ORDER BY date_depot DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteReclamation($id)
    {
        $stmt = $this->db->prepare("DELETE FROM reclamations WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function updateReclamation(Reclamation $r, $id)
    {
        $sql = "UPDATE reclamations SET
                    objet = ?,
                    type = ?,
                    ref_contrat = ?,
                    priorite = ?,
                    statut = ?,
                    date_depot = ?,
                    rec_ref = ?,
                    email = ?,
                    description = ?
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $r->getObjet(),
            $r->getType(),
            $r->getRefContrat(),
            $r->getPriorite(),
            $r->getStatut(),
            $r->getDateDepot()->format('Y-m-d H:i:s'),
            $r->getRecRef(),
            $r->getEmail(),
            $r->getDescription(),
            $id
        ]);
    }

    public function showReclamation($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM reclamations WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>