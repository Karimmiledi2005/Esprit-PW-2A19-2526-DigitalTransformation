<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/ReclamationModel.php';

class ReclamationController
{
    private $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    public function addReclamation(Reclamation $r)
    {
        $sql = "INSERT INTO reclamation
                (objet, type, ref_contrat, priorite, statut, date_depot, rec_ref, description, email)
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
            $r->getDescription(),
            $r->getEmail()
        ]);
    }

    public function listReclamations(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT id, objet, type, ref_contrat, priorite, statut,
                       date_depot, rec_ref, description, email
                FROM reclamation
                ORDER BY objet ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche des réclamations par objet (recherche partielle, insensible à la casse)
     * Résultats triés alphabétiquement par objet
     */
    public function searchByObjet(string $objet): array
    {
        $sql = "SELECT id, objet, type, ref_contrat, priorite, statut,
                       date_depot, rec_ref, description, email
                FROM reclamation
                WHERE objet LIKE :objet
                ORDER BY objet ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':objet', '%' . $objet . '%', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStats(): array
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(statut = 'open')     AS open_count,
                    SUM(statut = 'closed')   AS closed_count,
                    SUM(statut = 'rejected') AS rejected_count
                FROM reclamation";
        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

    public function countReclamations(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM reclamation")->fetchColumn();
    }

    public function deleteReclamation($id)
    {
        $stmt = $this->db->prepare("DELETE FROM reclamation WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function updateReclamation(Reclamation $r, $id)
    {
        $sql = "UPDATE reclamation SET
                    objet = ?, type = ?, ref_contrat = ?, priorite = ?,
                    statut = ?, date_depot = ?, rec_ref = ?, description = ?, email = ?
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $r->getObjet(), $r->getType(), $r->getRefContrat(), $r->getPriorite(),
            $r->getStatut(), $r->getDateDepot()->format('Y-m-d H:i:s'),
            $r->getRecRef(), $r->getDescription(), $r->getEmail(),
            $id
        ]);
    }

    public function showReclamation($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM reclamation WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
