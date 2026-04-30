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

    private function columnExists(string $table, string $column): bool
    {
        $sql = "SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = :table
                AND COLUMN_NAME = :column";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['table' => $table, 'column' => $column]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function selectSql(string $where = ''): string
    {
        $formuleSelect = $this->columnExists('contrat', 'id_formule') ? ', f.nom_formule, f.prix_formule, f.franchise_formule' : ', NULL AS nom_formule, NULL AS prix_formule, NULL AS franchise_formule';
        $formuleJoin = $this->columnExists('contrat', 'id_formule') ? 'LEFT JOIN formule f ON c.id_formule = f.id_formule' : '';

        return "SELECT c.*, cat.nom_categorie, u.nom, u.prenom, u.email $formuleSelect
                FROM contrat c
                LEFT JOIN categorie cat ON c.id_categorie = cat.id_categorie
                LEFT JOIN user u ON c.id_client = u.id_user
                $formuleJoin
                $where";
    }

    private function hydrate(array $row): Contrat
    {
        $contrat = new Contrat(
            $row['numero_contrat'],
            $row['type_contrat'],
            (int)$row['id_client'],
            (int)$row['id_categorie'],
            (float)$row['prime_contrat'],
            (float)$row['franchise_contrat'],
            $row['date_debut_contrat'],
            $row['date_fin_contrat'],
            $row['statut_contrat'],
            $row['id_formule'] ?? null,
            $row['formule_contrat'] ?? ($row['nom_formule'] ?? null),
            $row['details_contrat'] ?? null
        );

        $contrat->setIdContrat($row['id_contrat']);
        $contrat->setNomCategorie($row['nom_categorie'] ?? '—');
        $contrat->setNomFormule($row['nom_formule'] ?? ($row['formule_contrat'] ?? '—'));
        $contrat->setNomClient($row['nom'] ?? '');
        $contrat->setPrenomClient($row['prenom'] ?? '');
        $contrat->setEmailClient($row['email'] ?? '');
        return $contrat;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query($this->selectSql('ORDER BY c.id_contrat DESC'));
        return array_map(fn($row) => $this->hydrate($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getByClient(int $userId): array
    {
        if (!$userId) return [];
        $stmt = $this->db->prepare($this->selectSql('WHERE c.id_client = :id_client ORDER BY c.id_contrat DESC'));
        $stmt->execute(['id_client' => $userId]);
        return array_map(fn($row) => $this->hydrate($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): ?Contrat
    {
        $row = $this->getById($id);
        return $row ? $this->hydrate($row) : null;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare($this->selectSql('WHERE c.id_contrat = :id LIMIT 1'));
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getFirstClientId(): ?int
    {
        $stmt = $this->db->query("SELECT id_user FROM client ORDER BY id_user ASC LIMIT 1");
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    public function getAllFormules(): array
    {
        $stmt = $this->db->query("SELECT f.*, c.nom_categorie FROM formule f LEFT JOIN categorie c ON c.id_categorie = f.id_categorie ORDER BY c.nom_categorie ASC, f.id_formule ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFormulesByCategorie(int $idCategorie): array
    {
        $stmt = $this->db->prepare("SELECT * FROM formule WHERE id_categorie = :cat ORDER BY id_formule ASC");
        $stmt->execute(['cat' => $idCategorie]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFormuleById(int $idFormule): ?array
    {
        $stmt = $this->db->prepare("SELECT f.*, c.nom_categorie FROM formule f LEFT JOIN categorie c ON c.id_categorie = f.id_categorie WHERE f.id_formule = :id LIMIT 1");
        $stmt->execute(['id' => $idFormule]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getFormuleByNameAndCategorie(string $formuleName, int $idCategorie): ?array
    {
        $stmt = $this->db->prepare("SELECT f.*, c.nom_categorie FROM formule f LEFT JOIN categorie c ON c.id_categorie = f.id_categorie WHERE f.nom_formule = :nom AND f.id_categorie = :cat LIMIT 1");
        $stmt->execute(['nom' => $formuleName, 'cat' => $idCategorie]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function generateNumero(): string
    {
        do {
            $numero = 'CTR-' . date('Y') . '-' . str_pad((string)random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM contrat WHERE numero_contrat = :numero");
            $stmt->execute(['numero' => $numero]);
        } while ((int)$stmt->fetchColumn() > 0);
        return $numero;
    }

    public function addContrat($contrat): bool
    {
        $columns = "numero_contrat, type_contrat, id_client, id_categorie, prime_contrat, franchise_contrat, date_debut_contrat, date_fin_contrat, statut_contrat";
        $values  = ":numero_contrat, :type_contrat, :id_client, :id_categorie, :prime_contrat, :franchise_contrat, :date_debut_contrat, :date_fin_contrat, :statut_contrat";
        $params = [
            'numero_contrat' => $contrat->getNumeroContrat(),
            'type_contrat' => $contrat->getTypeContrat(),
            'id_client' => $contrat->getIdClient(),
            'id_categorie' => $contrat->getIdCategorie(),
            'prime_contrat' => $contrat->getPrimeContrat(),
            'franchise_contrat' => $contrat->getFranchiseContrat(),
            'date_debut_contrat' => $contrat->getDateDebutContrat(),
            'date_fin_contrat' => $contrat->getDateFinContrat(),
            'statut_contrat' => $contrat->getStatutContrat()
        ];

        if ($this->columnExists('contrat', 'id_formule')) {
            $columns .= ", id_formule";
            $values .= ", :id_formule";
            $params['id_formule'] = $contrat->getIdFormule();
        }
        if ($this->columnExists('contrat', 'formule_contrat')) {
            $columns .= ", formule_contrat";
            $values .= ", :formule_contrat";
            $params['formule_contrat'] = $contrat->getFormuleContrat();
        }
        if ($this->columnExists('contrat', 'details_contrat')) {
            $columns .= ", details_contrat";
            $values .= ", :details_contrat";
            $params['details_contrat'] = $contrat->getDetailsContrat();
        }

        $query = $this->db->prepare("INSERT INTO contrat ($columns) VALUES ($values)");
        return $query->execute($params);
    }

    public function updateContrat(int $id, $contrat): bool
    {
        $set = "numero_contrat = :numero_contrat,
                type_contrat = :type_contrat,
                id_client = :id_client,
                id_categorie = :id_categorie,
                prime_contrat = :prime_contrat,
                franchise_contrat = :franchise_contrat,
                date_debut_contrat = :date_debut_contrat,
                date_fin_contrat = :date_fin_contrat,
                statut_contrat = :statut_contrat";
        $params = [
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
        ];
        if ($this->columnExists('contrat', 'id_formule')) {
            $set .= ", id_formule = :id_formule";
            $params['id_formule'] = $contrat->getIdFormule();
        }
        if ($this->columnExists('contrat', 'formule_contrat')) {
            $set .= ", formule_contrat = :formule_contrat";
            $params['formule_contrat'] = $contrat->getFormuleContrat();
        }
        if ($this->columnExists('contrat', 'details_contrat')) {
            $set .= ", details_contrat = :details_contrat";
            $params['details_contrat'] = $contrat->getDetailsContrat();
        }
        $query = $this->db->prepare("UPDATE contrat SET $set WHERE id_contrat = :id");
        return $query->execute($params);
    }

    public function updateStatut(int $id, string $statut): bool
    {
        $allowed = ['en attente', 'actif', 'expiré', 'résilié', 'refusé'];
        if (!in_array($statut, $allowed, true)) return false;
        $stmt = $this->db->prepare("UPDATE contrat SET statut_contrat = :statut WHERE id_contrat = :id");
        return $stmt->execute(['statut' => $statut, 'id' => $id]);
    }

    public function deleteContrat(int $id): bool
    {
        $query = $this->db->prepare("DELETE FROM contrat WHERE id_contrat = :id");
        return $query->execute(['id' => $id]);
    }

    public function countContrats(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM contrat")->fetchColumn();
    }
}
