<?php
require_once __DIR__ . '/../config/database.php';
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
            SELECT 
                c.*,
                cat.nom_categorie,
                u.nom,
                u.prenom,
                u.email
            FROM contrat c
            LEFT JOIN categorie cat ON c.id_categorie = cat.id_categorie
            LEFT JOIN user u ON c.id_client = u.id_user
            ORDER BY c.date_debut_contrat DESC
        ");

        $contrats = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $contrat = new Contrat(
                $row['numero_contrat'],
                $row['type_contrat'],
                $row['id_client'],
                $row['id_categorie'],
                $row['prime_contrat'],
                $row['franchise_contrat'],
                $row['date_debut_contrat'],
                $row['date_fin_contrat'],
                $row['statut_contrat']
            );

            $contrat->setIdContrat($row['id_contrat']);
            $contrat->setNomCategorie($row['nom_categorie'] ?? '—');
            $contrat->setNomClient($row['nom'] ?? '');
            $contrat->setPrenomClient($row['prenom'] ?? '');
            $contrat->setEmailClient($row['email'] ?? '');

            $contrats[] = $contrat;
        }

        return $contrats;
    }

    public function getByClient(int $userId): array
    {
        if (!$userId) return [];

        $stmt = $this->db->prepare("
            SELECT 
                c.*,
                cat.nom_categorie
            FROM contrat c
            LEFT JOIN categorie cat ON c.id_categorie = cat.id_categorie
            WHERE c.id_client = :id_client
            ORDER BY c.date_debut_contrat DESC
        ");
        $stmt->execute(['id_client' => $userId]);

        $contrats = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $contrat = new Contrat(
                $row['numero_contrat'],
                $row['type_contrat'],
                $row['id_client'],
                $row['id_categorie'],
                $row['prime_contrat'],
                $row['franchise_contrat'],
                $row['date_debut_contrat'],
                $row['date_fin_contrat'],
                $row['statut_contrat']
            );

            $contrat->setIdContrat($row['id_contrat']);
            $contrat->setNomCategorie($row['nom_categorie'] ?? '—');

            $contrats[] = $contrat;
        }

        return $contrats;
    }

    public function getStats(): array
    {
        $contrats = $this->getAll();

        $stats = [
            'total' => count($contrats),
            'actifs' => 0,
            'attente' => 0,
            'expires' => 0
        ];

        foreach ($contrats as $contrat) {
            $statut = strtolower(trim($contrat->getStatutContrat()));

            if ($statut === 'actif') {
                $stats['actifs']++;
            } elseif ($statut === 'en attente' || $statut === 'en_attente') {
                $stats['attente']++;
            } elseif ($statut === 'expire' || $statut === 'expiré' || $statut === 'resilie' || $statut === 'résilié') {
                $stats['expires']++;
            }
        }

        return $stats;
    }

    public function addContrat($contrat): bool
    {
        $sql = "INSERT INTO contrat (numero_contrat, type_contrat, id_client, id_categorie, 
                                     prime_contrat, franchise_contrat, date_debut_contrat, 
                                     date_fin_contrat, statut_contrat)
                VALUES (:numero_contrat, :type_contrat, :id_client, :id_categorie,
                        :prime_contrat, :franchise_contrat, :date_debut_contrat,
                        :date_fin_contrat, :statut_contrat)";

        try {
            $query = $this->db->prepare($sql);
            return $query->execute([
                'numero_contrat' => $contrat->getNumeroContrat(),
                'type_contrat' => $contrat->getTypeContrat(),
                'id_client' => $contrat->getIdClient(),
                'id_categorie' => $contrat->getIdCategorie(),
                'prime_contrat' => $contrat->getPrimeContrat(),
                'franchise_contrat' => $contrat->getFranchiseContrat(),
                'date_debut_contrat' => $contrat->getDateDebutContrat(),
                'date_fin_contrat' => $contrat->getDateFinContrat(),
                'statut_contrat' => $contrat->getStatutContrat()
            ]);
        } catch (Exception $e) {
            die('Erreur addContrat: ' . $e->getMessage());
        }
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT c.*, cat.nom_categorie, u.nom, u.prenom, u.email
                FROM contrat c
                LEFT JOIN categorie cat ON c.id_categorie = cat.id_categorie
                LEFT JOIN user u ON c.id_client = u.id_user
                WHERE c.id_contrat = :id";

        try {
            $query = $this->db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur getById: ' . $e->getMessage());
        }
    }

    public function updateContrat(int $id, $contrat): bool
    {
        $sql = "UPDATE contrat
                SET numero_contrat = :numero_contrat,
                    type_contrat = :type_contrat,
                    id_client = :id_client,
                    id_categorie = :id_categorie,
                    prime_contrat = :prime_contrat,
                    franchise_contrat = :franchise_contrat,
                    date_debut_contrat = :date_debut_contrat,
                    date_fin_contrat = :date_fin_contrat,
                    statut_contrat = :statut_contrat
                WHERE id_contrat = :id";

        try {
            $query = $this->db->prepare($sql);
            return $query->execute([
                'id' => $id,
                'numero_contrat' => $contrat->getNumeroContrat(),
                'type_contrat' => $contrat->getTypeContrat(),
                'id_client' => $contrat->getIdClient(),
                'id_categorie' => $contrat->getIdCategorie(),
                'prime_contrat' => $contrat->getPrimeContrat(),
                'franchise_contrat' => $contrat->getFranchiseContrat(),
                'date_debut_contrat' => $contrat->getDateDebutContrat(),
                'date_fin_contrat' => $contrat->getDateFinContrat(),
                'statut_contrat' => $contrat->getStatutContrat()
            ]);
        } catch (Exception $e) {
            die('Erreur updateContrat: ' . $e->getMessage());
        }
    }

    public function deleteContrat(int $id): bool
    {
        $sql = "DELETE FROM contrat WHERE id_contrat = :id";

        try {
            $query = $this->db->prepare($sql);
            return $query->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur deleteContrat: ' . $e->getMessage());
        }
    }

    public function countContrats(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM contrat";

        try {
            $query = $this->db->query($sql);
            $row = $query->fetch(PDO::FETCH_ASSOC);
            return (int)$row['total'];
        } catch (Exception $e) {
            die('Erreur countContrats: ' . $e->getMessage());
        }
    }
}