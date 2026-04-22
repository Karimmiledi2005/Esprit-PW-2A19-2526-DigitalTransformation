<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

class PaiementController
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = BASE_URL . '/controller/PaiementController.php';
    }

    private function redirectToIndex(string $message = '', string $erreur = ''): void
    {
        $params = ['action' => 'index'];

        if ($message !== '') {
            $params['message'] = $message;
        }

        if ($erreur !== '') {
            $params['erreur'] = $erreur;
        }

        header('Location: ' . $this->baseUrl . '?' . http_build_query($params));
        exit;
    }

    private function redirectToAction(string $action, int $id, string $message = '', string $erreur = ''): void
    {
        $params = [
            'action' => $action,
            'id' => $id
        ];

        if ($message !== '') {
            $params['message'] = $message;
        }

        if ($erreur !== '') {
            $params['erreur'] = $erreur;
        }

        header('Location: ' . $this->baseUrl . '?' . http_build_query($params));
        exit;
    }

    private function getPaiementById(int $id): ?array
    {
        $db = config::getConnexion();

        $sql = "
            SELECT
                p.*,
                o.nom_offre,
                o.type_offre
            FROM paiement p
            LEFT JOIN offre o ON o.id_offre = p.id_offre
            WHERE p.id_paiement = :id
            LIMIT 1
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $paiement = $stmt->fetch(PDO::FETCH_ASSOC);

        return $paiement ?: null;
    }

    public function index(): void
    {
        $db      = config::getConnexion();
        $message = $_GET['message'] ?? '';
        $erreur  = $_GET['erreur'] ?? '';

        try {
            $sql = "
                SELECT
                    p.*,
                    o.nom_offre,
                    o.type_offre
                FROM paiement p
                LEFT JOIN offre o ON o.id_offre = p.id_offre
                ORDER BY p.date_paiement DESC, p.id_paiement DESC
            ";
            $stmt = $db->query($sql);
            $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $paiements = [];
            $erreur = 'Erreur lors du chargement des paiements : ' . $e->getMessage();
        }

        try {
            $sqlStats = "
                SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) AS en_attente,
                    SUM(CASE WHEN statut = 'valide' THEN 1 ELSE 0 END) AS valides,
                    SUM(CASE WHEN statut = 'refuse' THEN 1 ELSE 0 END) AS refuses,
                    SUM(CASE WHEN statut = 'rembourse' THEN 1 ELSE 0 END) AS rembourses,
                    COALESCE(SUM(CASE WHEN statut = 'valide' THEN montant ELSE 0 END), 0) AS montant_total_valide
                FROM paiement
            ";
            $stmtStats = $db->query($sqlStats);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            $stats = [
                'total' => 0,
                'en_attente' => 0,
                'valides' => 0,
                'refuses' => 0,
                'rembourses' => 0,
                'montant_total_valide' => 0
            ];
        }

        try {
            $sqlEcheances = "
                SELECT
                    p.id_paiement,
                    p.reference_paiement,
                    p.date_echeance
                FROM paiement p
                WHERE p.date_echeance IS NOT NULL
                  AND DATE(p.date_echeance) >= CURDATE()
                  AND DATE(p.date_echeance) <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                  AND p.statut IN ('en_attente', 'valide')
                ORDER BY p.date_echeance ASC
                LIMIT 8
            ";
            $stmtEcheances = $db->query($sqlEcheances);
            $echeances = $stmtEcheances->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $echeances = [];
        }

        require_once __DIR__ . '/../view/BackOffice/paiements/liste.php';
    }

    public function detail(): void
    {
        $id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $message = $_GET['message'] ?? '';
        $erreur  = $_GET['erreur'] ?? '';

        if ($id <= 0) {
            $this->redirectToIndex('', 'Identifiant de paiement invalide');
        }

        $paiement = $this->getPaiementById($id);

        if (!$paiement) {
            $this->redirectToIndex('', 'Paiement introuvable');
        }

        require_once __DIR__ . '/../view/BackOffice/paiements/detail.php';
    }

    public function valider(): void
    {
        $id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $message = $_GET['message'] ?? '';
        $erreur  = $_GET['erreur'] ?? '';

        if ($id <= 0) {
            $this->redirectToIndex('', 'Identifiant de paiement invalide');
        }

        $paiement = $this->getPaiementById($id);

        if (!$paiement) {
            $this->redirectToIndex('', 'Paiement introuvable');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $db = config::getConnexion();

                if (($paiement['statut'] ?? '') !== 'en_attente') {
                    $this->redirectToIndex('', 'Seuls les paiements en attente peuvent être validés');
                }

                $stmt = $db->prepare("
                    UPDATE paiement
                    SET statut = 'valide'
                    WHERE id_paiement = :id
                ");
                $stmt->execute([':id' => $id]);

                $this->redirectToIndex('Paiement validé avec succès');
            } catch (Exception $e) {
                $this->redirectToAction('valider', $id, '', 'Erreur lors de la validation : ' . $e->getMessage());
            }
        }

        require_once __DIR__ . '/../view/BackOffice/paiements/valider.php';
    }

    public function refuser(): void
    {
        $id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $message = $_GET['message'] ?? '';
        $erreur  = $_GET['erreur'] ?? '';

        if ($id <= 0) {
            $this->redirectToIndex('', 'Identifiant de paiement invalide');
        }

        $paiement = $this->getPaiementById($id);

        if (!$paiement) {
            $this->redirectToIndex('', 'Paiement introuvable');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $db = config::getConnexion();

                if (($paiement['statut'] ?? '') !== 'en_attente') {
                    $this->redirectToIndex('', 'Seuls les paiements en attente peuvent être refusés');
                }

                $stmt = $db->prepare("
                    UPDATE paiement
                    SET statut = 'refuse'
                    WHERE id_paiement = :id
                ");
                $stmt->execute([':id' => $id]);

                $this->redirectToIndex('Paiement refusé avec succès');
            } catch (Exception $e) {
                $this->redirectToAction('refuser', $id, '', 'Erreur lors du refus : ' . $e->getMessage());
            }
        }

        require_once __DIR__ . '/../view/BackOffice/paiements/refuser.php';
    }

    public function rembourser(): void
    {
        $id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $message = $_GET['message'] ?? '';
        $erreur  = $_GET['erreur'] ?? '';

        if ($id <= 0) {
            $this->redirectToIndex('', 'Identifiant de paiement invalide');
        }

        $paiement = $this->getPaiementById($id);

        if (!$paiement) {
            $this->redirectToIndex('', 'Paiement introuvable');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $db = config::getConnexion();

                if (($paiement['statut'] ?? '') !== 'valide') {
                    $this->redirectToIndex('', 'Seuls les paiements validés peuvent être remboursés');
                }

                $stmt = $db->prepare("
                    UPDATE paiement
                    SET statut = 'rembourse'
                    WHERE id_paiement = :id
                ");
                $stmt->execute([':id' => $id]);

                $this->redirectToIndex('Paiement remboursé avec succès');
            } catch (Exception $e) {
                $this->redirectToAction('rembourser', $id, '', 'Erreur lors du remboursement : ' . $e->getMessage());
            }
        }

        require_once __DIR__ . '/../view/BackOffice/paiements/rembourser.php';
    }
}

$controller = new PaiementController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'detail':
        $controller->detail();
        break;

    case 'valider':
        $controller->valider();
        break;

    case 'refuser':
        $controller->refuser();
        break;

    case 'rembourser':
        $controller->rembourser();
        break;

    default:
        $controller->index();
        break;
}