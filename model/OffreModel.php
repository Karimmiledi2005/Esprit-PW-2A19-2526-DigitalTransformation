<?php
/* =============================================
   OffreModel.php — CRUD Offre
   Protex Assurance
   ============================================= */

require_once __DIR__ . '/../config/config.php';

class OffreModel {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /* =============================================
       READ — Lister toutes les offres (admin)
       ============================================= */
    public function getAll(): array {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM offre
            ORDER BY date_creation DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /* =============================================
       READ — Lister offres actives (client)
       ============================================= */
    public function getActives(): array {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM offre
            WHERE statut = 'active'
            ORDER BY prix_mensuel ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /* =============================================
       READ — Offres actives par type (client)
       ============================================= */
    public function getByType(string $type): array {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM offre
            WHERE statut = 'active'
            AND type_offre = ?
            ORDER BY prix_mensuel ASC
        ");
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }

    /* =============================================
       READ — Une offre par ID
       ============================================= */
    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM offre
            WHERE id_offre = ?
        ");
        $stmt->execute([$id]);
        $offre = $stmt->fetch();
        return $offre ?: null;
    }

    /* =============================================
       CREATE — Ajouter une offre (admin)
       ============================================= */
    public function ajouter(array $data): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO offre
            (nom_offre, type_offre, description,
             prix_mensuel, prix_annuel, couverture,
             plafond, duree_min, statut)
            VALUES
            (:nom_offre, :type_offre, :description,
             :prix_mensuel, :prix_annuel, :couverture,
             :plafond, :duree_min, :statut)
        ");

        return $stmt->execute([
            ':nom_offre'    => $data['nom_offre'],
            ':type_offre'   => $data['type_offre'],
            ':description'  => $data['description'],
            ':prix_mensuel' => $data['prix_mensuel'],
            ':prix_annuel'  => $data['prix_annuel'],
            ':couverture'   => $data['couverture'],
            ':plafond'      => $data['plafond'] ?? null,
            ':duree_min'    => $data['duree_min'] ?? 1,
            ':statut'       => $data['statut'] ?? 'active',
        ]);
    }

    /* =============================================
       UPDATE — Modifier une offre (admin)
       ============================================= */
    public function modifier(int $id, array $data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE offre SET
                nom_offre    = :nom_offre,
                type_offre   = :type_offre,
                description  = :description,
                prix_mensuel = :prix_mensuel,
                prix_annuel  = :prix_annuel,
                couverture   = :couverture,
                plafond      = :plafond,
                duree_min    = :duree_min,
                statut       = :statut
            WHERE id_offre   = :id_offre
        ");

        return $stmt->execute([
            ':nom_offre'    => $data['nom_offre'],
            ':type_offre'   => $data['type_offre'],
            ':description'  => $data['description'],
            ':prix_mensuel' => $data['prix_mensuel'],
            ':prix_annuel'  => $data['prix_annuel'],
            ':couverture'   => $data['couverture'],
            ':plafond'      => $data['plafond'] ?? null,
            ':duree_min'    => $data['duree_min'] ?? 1,
            ':statut'       => $data['statut'],
            ':id_offre'     => $id,
        ]);
    }

    /* =============================================
       DELETE — Supprimer une offre (admin)
       ============================================= */
    public function supprimer(int $id): bool {

        /* Vérifier si des paiements existent */
        $check = $this->pdo->prepare("
            SELECT COUNT(*) FROM paiement
            WHERE id_offre = ?
        ");
        $check->execute([$id]);
        $count = $check->fetchColumn();

        /* Si paiements existent → archiver seulement */
        if ($count > 0) {
            return $this->archiver($id);
        }

        /* Sinon → supprimer définitivement */
        $stmt = $this->pdo->prepare("
            DELETE FROM offre
            WHERE id_offre = ?
        ");
        return $stmt->execute([$id]);
    }

    /* =============================================
       UPDATE — Archiver une offre (admin)
       ============================================= */
    public function archiver(int $id): bool {
        $stmt = $this->pdo->prepare("
            UPDATE offre
            SET statut = 'archivee'
            WHERE id_offre = ?
        ");
        return $stmt->execute([$id]);
    }

    /* =============================================
       UPDATE — Changer statut (admin)
       ============================================= */
    public function changerStatut(int $id, string $statut): bool {
        $stmt = $this->pdo->prepare("
            UPDATE offre
            SET statut = ?
            WHERE id_offre = ?
        ");
        return $stmt->execute([$statut, $id]);
    }

    /* =============================================
       READ — Statistiques (admin dashboard)
       ============================================= */
    public function getStats(): array {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*)                                AS total,
                SUM(statut = 'active')                  AS actives,
                SUM(statut = 'suspendue')               AS suspendues,
                SUM(statut = 'archivee')                AS archivees
            FROM offre
        ");
        $stmt->execute();
        return $stmt->fetch();
    }
}