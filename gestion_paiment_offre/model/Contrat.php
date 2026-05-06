<?php
/**
 * model/Contrat.php
 * Gestion des contrats — Protex 2026
 */

class Contrat
{
    public static function createFromDevis(PDO $db, array $devis, string $periodicite, string $notes = ''): array
    {
        $numeroContrat = 'CTR-' . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 8));
        $montant = $periodicite === 'annuel'
            ? (float)($devis['prix_annuel'] ?? 0)
            : (float)($devis['prix_mensuel'] ?? 0);

        if ($montant <= 0 && isset($devis['montant_estime'])) {
            $montant = (float)$devis['montant_estime'];
        }

        $dateDebut = date('Y-m-d');
        $dateFin = $periodicite === 'annuel'
            ? date('Y-m-d', strtotime('+1 year'))
            : date('Y-m-d', strtotime('+1 month'));

        $stmt = $db->prepare("
            INSERT INTO contrat
                (numero_contrat, id_devis, id_categorie, type_contrat, prime_contrat, date_debut_contrat, date_fin_contrat, statut_contrat, notes)
            VALUES
                (:numero, :id_devis, :id_categorie, :type_contrat, :prime, :date_debut, :date_fin, 'actif', :notes)
        ");

        $stmt->execute([
            ':numero'        => $numeroContrat,
            ':id_devis'      => isset($devis['id_devis']) ? (int)$devis['id_devis'] : null,
            ':id_categorie'  => (int)($devis['id_offre'] ?? 0),
            ':type_contrat'  => $periodicite,
            ':prime'         => $montant,
            ':date_debut'    => $dateDebut,
            ':date_fin'      => $dateFin,
            ':notes'         => $notes ?: null,
        ]);

        $idContrat = (int)$db->lastInsertId();

        $db->prepare("UPDATE devis SET statut = 'en_cours' WHERE id_devis = ?")->execute([$devis['id_devis']]);

        return [
            'id_contrat'   => $idContrat,
            'numero_contrat' => $numeroContrat,
            'prime_contrat'  => $montant,
            'date_debut_contrat' => $dateDebut,
            'date_fin_contrat'   => $dateFin,
        ];
    }

    public static function getAll(PDO $db, array $filters = []): array
    {
        $sql = "
            SELECT c.*,
                   d.prenom, d.nom, d.email, d.telephone,
                   o.nom_offre
            FROM contrat c
            LEFT JOIN devis d ON d.id_devis = c.id_devis
            LEFT JOIN offre o ON o.id_offre = c.id_categorie
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['statut'])) {
            $sql .= " AND c.statut_contrat = ?";
            $params[] = $filters['statut'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (c.numero_contrat LIKE ? OR d.nom LIKE ? OR d.prenom LIKE ? OR d.email LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$s, $s, $s, $s]);
        }

        $sql .= " ORDER BY c.date_debut_contrat DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById(PDO $db, int $id): ?array
    {
        $stmt = $db->prepare("
            SELECT c.*,
                   d.prenom, d.nom, d.email, d.telephone,
                   d.type_assurance,
                   o.nom_offre, o.couverture, o.prix_mensuel, o.prix_annuel
            FROM contrat c
            LEFT JOIN devis d ON d.id_devis = c.id_devis
            LEFT JOIN offre o ON o.id_offre = c.id_categorie
            WHERE c.id_contrat = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function getByReference(PDO $db, string $ref): ?array
    {
        $stmt = $db->prepare("SELECT * FROM contrat WHERE numero_contrat = ?");
        $stmt->execute([$ref]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function updateStatut(PDO $db, int $id, string $statut): bool
    {
        $stmt = $db->prepare("UPDATE contrat SET statut_contrat = ? WHERE id_contrat = ?");
        return $stmt->execute([$statut, $id]);
    }

    public static function getStats(PDO $db): array
    {
        $stmt = $db->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN statut_contrat = 'actif' THEN 1 ELSE 0 END) as actifs,
                SUM(CASE WHEN statut_contrat = 'expire' THEN 1 ELSE 0 END) as expires,
                SUM(CASE WHEN statut_contrat = 'resilie' THEN 1 ELSE 0 END) as resilies,
                SUM(CASE WHEN statut_contrat = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                COALESCE(SUM(CASE WHEN statut_contrat = 'actif' THEN prime_contrat ELSE 0 END), 0) as montant_total_actif
            FROM contrat
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getRecent(PDO $db, int $limit = 10): array
    {
        $stmt = $db->prepare("
            SELECT c.*, d.prenom, d.nom, o.nom_offre
            FROM contrat c
            LEFT JOIN devis d ON d.id_devis = c.id_devis
            LEFT JOIN offre o ON o.id_offre = c.id_categorie
            ORDER BY c.date_debut_contrat DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
