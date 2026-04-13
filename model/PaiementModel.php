<?php
/* =============================================
   PaiementModel.php — CRUD Paiement
   Protex Assurance
   ============================================= */

require_once __DIR__ . '/../config/config.php';

class PaiementModel {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /* =============================================
       CREATE — Créer un paiement (client)
       ============================================= */
    public function creer(array $data): bool {

        /* Masquer numéro carte */
        $carte_masque = null;
        if (!empty($data['num_carte'])) {
            $carte = preg_replace('/\s+/', '', $data['num_carte']);
            $carte_masque = '**** **** **** ' . substr($carte, -4);
        }

        /* Calculer date échéance */
        $date_echeance = ($data['periodicite'] === 'annuel')
            ? date('Y-m-d', strtotime('+1 year'))
            : date('Y-m-d', strtotime('+1 month'));

        /* Générer référence unique */
        $reference = 'PTX-' . strtoupper(substr(md5(uniqid()), 0, 8));

        $stmt = $this->pdo->prepare("
            INSERT INTO paiement
            (id_offre, reference, montant, methode,
             periodicite, statut, date_echeance,
             num_carte_masque)
            VALUES
            (:id_offre, :reference, :montant, :methode,
             :periodicite, 'en_attente', :date_echeance,
             :num_carte_masque)
        ");

        return $stmt->execute([
            ':id_offre'         => $data['id_offre'],
            ':reference'        => $reference,
            ':montant'          => $data['montant'],
            ':methode'          => $data['methode'],
            ':periodicite'      => $data['periodicite'],
            ':date_echeance'    => $date_echeance,
            ':num_carte_masque' => $carte_masque,
        ]);
    }

    /* =============================================
       READ — Tous les paiements (admin)
       ============================================= */
    public function getAll(): array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, o.nom_offre, o.type_offre
            FROM paiement p
            JOIN offre o ON p.id_offre = o.id_offre
            ORDER BY p.date_paiement DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /* =============================================
       READ — Un paiement par ID
       ============================================= */
    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, o.nom_offre, o.type_offre,
                   o.couverture, o.prix_mensuel, o.prix_annuel
            FROM paiement p
            JOIN offre o ON p.id_offre = o.id_offre
            WHERE p.id_paiement = ?
        ");
        $stmt->execute([$id]);
        $paiement = $stmt->fetch();
        return $paiement ?: null;
    }

    /* =============================================
       READ — Paiements par offre
       ============================================= */
    public function getByOffre(int $idOffre): array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, o.nom_offre
            FROM paiement p
            JOIN offre o ON p.id_offre = o.id_offre
            WHERE p.id_offre = ?
            ORDER BY p.date_paiement DESC
        ");
        $stmt->execute([$idOffre]);
        return $stmt->fetchAll();
    }

    /* =============================================
       READ — Paiements par statut (admin)
       ============================================= */
    public function getByStatut(string $statut): array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, o.nom_offre, o.type_offre
            FROM paiement p
            JOIN offre o ON p.id_offre = o.id_offre
            WHERE p.statut = ?
            ORDER BY p.date_paiement DESC
        ");
        $stmt->execute([$statut]);
        return $stmt->fetchAll();
    }

    /* =============================================
       READ — Référence unique
       ============================================= */
    public function getByReference(string $ref): ?array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, o.nom_offre
            FROM paiement p
            JOIN offre o ON p.id_offre = o.id_offre
            WHERE p.reference = ?
        ");
        $stmt->execute([$ref]);
        $paiement = $stmt->fetch();
        return $paiement ?: null;
    }

    /* =============================================
       UPDATE — Valider un paiement (admin)
       ============================================= */
    public function valider(int $id): bool {
        $stmt = $this->pdo->prepare("
            UPDATE paiement
            SET statut = 'valide'
            WHERE id_paiement = ?
            AND statut = 'en_attente'
        ");
        return $stmt->execute([$id]);
    }

    /* =============================================
       UPDATE — Refuser un paiement (admin)
       ============================================= */
    public function refuser(int $id, string $motif = ''): bool {
        $stmt = $this->pdo->prepare("
            UPDATE paiement
            SET statut = 'refuse',
                motif_refus = ?
            WHERE id_paiement = ?
            AND statut = 'en_attente'
        ");
        return $stmt->execute([$motif, $id]);
    }

    /* =============================================
       UPDATE — Rembourser un paiement (admin)
       ============================================= */
    public function rembourser(int $id, string $motif = ''): bool {
        $stmt = $this->pdo->prepare("
            UPDATE paiement
            SET statut = 'rembourse',
                motif_refus = ?
            WHERE id_paiement = ?
            AND statut = 'valide'
        ");
        return $stmt->execute([$motif, $id]);
    }

    /* =============================================
       READ — Statistiques (admin dashboard)
       ============================================= */
    public function getStats(): array {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*)                                  AS total,
                SUM(statut = 'en_attente')                AS en_attente,
                SUM(statut = 'valide')                    AS valides,
                SUM(statut = 'refuse')                    AS refuses,
                SUM(statut = 'rembourse')                 AS rembourses,
                SUM(CASE WHEN statut = 'valide'
                    THEN montant ELSE 0 END)              AS chiffre_affaires,
                SUM(CASE WHEN statut = 'valide'
                    AND MONTH(date_paiement) = MONTH(NOW())
                    AND YEAR(date_paiement)  = YEAR(NOW())
                    THEN montant ELSE 0 END)              AS ca_ce_mois
            FROM paiement
        ");
        $stmt->execute();
        return $stmt->fetch();
    }

    /* =============================================
       READ — Paiements échéance proche (3 jours)
       ============================================= */
    public function getEcheancesProches(): array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, o.nom_offre
            FROM paiement p
            JOIN offre o ON p.id_offre = o.id_offre
            WHERE p.statut = 'valide'
            AND p.date_echeance BETWEEN CURDATE()
            AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
            ORDER BY p.date_echeance ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}