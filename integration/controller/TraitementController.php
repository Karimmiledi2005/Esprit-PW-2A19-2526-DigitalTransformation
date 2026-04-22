<?php
require_once __DIR__ . '/../connexion.php';
require_once __DIR__ . '/../model/Traitement.php';

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
            SELECT t.id_traitement, t.decision, t.montant_indemnise, t.statut, t.date_traitement,
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

        if (!$idSinistre) return ['success' => false, 'message' => 'ID sinistre requis.'];
        if (!$decision) return ['success' => false, 'message' => 'Decision requise.'];
        if ($this->traitementExists($idSinistre)) {
            return ['success' => false, 'message' => "Le sinistre #$idSinistre a deja un traitement enregistre.", 'code' => 409];
        }

        $stmt = $this->db->prepare("
            INSERT INTO traitement (id_sinistre, id_user, nom_agent, decision, montant_indemnise, statut, date_traitement)
            VALUES (:id_sinistre, :id_user, :nom_agent, :decision, :montant, :statut, CURDATE())
        ");
        $stmt->execute([
            ':id_sinistre' => $idSinistre,
            ':id_user'     => $userId,
            ':nom_agent'   => $nomAgent ?: null,
            ':decision'    => $decision,
            ':montant'     => $montant,
            ':statut'      => $statut,
        ]);

        $id = (int)$this->db->lastInsertId();

        // Update sinistre statut
        if (in_array($statut, ['accepte', 'refuse'])) {
            $newStat = $statut === 'accepte' ? 'rembourse' : 'refuse';
            $s = $this->db->prepare("UPDATE sinistre SET statut=:s WHERE id_sinistre=:id");
            $s->execute([':s' => $newStat, ':id' => $idSinistre]);
        }

        return ['success' => true, 'message' => 'Traitement enregistre.', 'id' => $id];
    }

    public function update(int $id, array $data): array
    {
        if (!$id) return ['success' => false, 'message' => 'ID manquant.'];
        
        $montantRaw = isset($data['montant']) ? trim($data['montant']) : '';
        $montant    = ($montantRaw !== '' && is_numeric($montantRaw)) ? (float)$montantRaw : null;

        $stmt = $this->db->prepare("
            UPDATE traitement SET nom_agent=:nom_agent, decision=:decision, montant_indemnise=:montant, statut=:statut
            WHERE id_traitement=:id
        ");
        $stmt->execute([
            ':nom_agent' => trim($data['nom_agent'] ?? ''),
            ':decision'  => trim($data['decision']  ?? ''),
            ':montant'   => $montant,
            ':statut'    => $data['statut'] ?? 'en_cours',
            ':id'        => $id,
        ]);

        return ['success' => true, 'message' => 'Traitement mis a jour.'];
    }

    public function delete(int $id): array
    {
        if (!$id) return ['success' => false, 'message' => 'ID manquant.'];
        
        $stmt = $this->db->prepare("DELETE FROM traitement WHERE id_traitement=:id");
        $stmt->execute([':id' => $id]);
        return ['success' => true, 'message' => 'Traitement supprime.'];
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
