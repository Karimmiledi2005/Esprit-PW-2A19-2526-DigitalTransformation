<?php
require_once __DIR__ . '/../connexion.php';
require_once __DIR__ . '/../model/Contrat.php';

class ContratController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT c.*, cat.nom_categorie, u.nom, u.prenom, u.email
            FROM contrat c
            LEFT JOIN categorie cat ON c.id_categorie = cat.id_categorie
            LEFT JOIN user u ON c.id_client = u.id_user
            ORDER BY c.date_debut_contrat DESC
        ");
        $contrats = [];
        foreach ($stmt->fetchAll() as $row) {
            $contrat = new Contrat($row['numero_contrat'], $row['type_contrat'], $row['id_client'], $row['id_categorie']);
            $contrat->setIdContrat($row['id_contrat']);
            $contrat->setDateDebutContrat($row['date_debut_contrat']);
            $contrat->setDateFinContrat($row['date_fin_contrat']);
            $contrat->setPrimeContrat($row['prime_contrat']);
            $contrat->setFranchiseContrat($row['franchise_contrat']);
            $contrat->setStatutContrat($row['statut_contrat']);
            $contrats[] = $contrat;
        }
        return $contrats;
    }

    public function getByClient(int $userId): array
    {
        if (!$userId) return [];
        $stmt = $this->db->prepare("
            SELECT c.*, cat.nom_categorie
            FROM contrat c
            LEFT JOIN categorie cat ON c.id_categorie = cat.id_categorie
            WHERE c.id_client = :id_client
            ORDER BY c.date_debut_contrat DESC
        ");
        $stmt->execute([':id_client' => $userId]);
        $contrats = [];
        foreach ($stmt->fetchAll() as $row) {
            $contrat = new Contrat($row['numero_contrat'], $row['type_contrat'], $row['id_client'], $row['id_categorie']);
            $contrat->setIdContrat($row['id_contrat']);
            $contrat->setDateDebutContrat($row['date_debut_contrat']);
            $contrat->setDateFinContrat($row['date_fin_contrat']);
            $contrat->setPrimeContrat($row['prime_contrat']);
            $contrat->setFranchiseContrat($row['franchise_contrat']);
            $contrat->setStatutContrat($row['statut_contrat']);
            $contrats[] = $contrat;
        }
        return $contrats;
    }

    public function findById(int $id): ?Contrat
    {
        if (!$id) return null;
        $stmt = $this->db->prepare("SELECT * FROM contrat WHERE id_contrat = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        
        $contrat = new Contrat($row['numero_contrat'], $row['type_contrat'], $row['id_client'], $row['id_categorie']);
        $contrat->setIdContrat($row['id_contrat']);
        $contrat->setDateDebutContrat($row['date_debut_contrat']);
        $contrat->setDateFinContrat($row['date_fin_contrat']);
        $contrat->setPrimeContrat($row['prime_contrat']);
        $contrat->setFranchiseContrat($row['franchise_contrat']);
        $contrat->setStatutContrat($row['statut_contrat']);
        return $contrat;
    }
}
