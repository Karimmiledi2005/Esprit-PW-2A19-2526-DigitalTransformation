<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../model/Paiement.php');
include(__DIR__ . '/../model/Offre.php');

class PaiementController
{
    private string $baseUrl;

    public function __construct()
    {
        $root = defined('BASE_URL') ? BASE_URL : '/projet_web1/gestion_paiment_offre';
        $this->baseUrl = $root . '/controller/PaiementController.php';
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

    private function getPaiementById(int $id)
    {
        $db = config::getConnexion();

        $stmt = $db->prepare("
            SELECT p.*, o.nom_offre, o.type_offre, o.description AS offre_description
            FROM paiement p
            LEFT JOIN offre o ON p.id_offre = o.id_offre
            WHERE p.id_paiement = ?
        ");
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    public function index(): void
    {
        $db      = config::getConnexion();
        $message = $_GET['message'] ?? '';
        $erreur  = $_GET['erreur'] ?? '';

        try {
            $paiements = $db->query("
                SELECT p.*, o.nom_offre, o.type_offre
                FROM paiement p
                LEFT JOIN offre o ON p.id_offre = o.id_offre
                ORDER BY p.date_paiement DESC
            ")->fetchAll();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }

        try {
            $stats = $db->query("
                SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) AS en_attente,
                    SUM(CASE WHEN statut = 'valide' THEN 1 ELSE 0 END) AS valides,
                    SUM(CASE WHEN statut = 'refuse' THEN 1 ELSE 0 END) AS refuses,
                    SUM(CASE WHEN statut = 'rembourse' THEN 1 ELSE 0 END) AS rembourses,
                    SUM(CASE WHEN statut = 'valide' THEN montant ELSE 0 END) AS chiffre_affaires
                FROM paiement
            ")->fetch();

            if (!$stats) {
                $stats = [];
            }
        } catch (Exception $e) {
            $stats = [];
        }

        try {
            $echeances = $db->query("
                SELECT reference, date_echeance
                FROM paiement
                WHERE date_echeance BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                  AND statut = 'valide'
            ")->fetchAll();
        } catch (Exception $e) {
            $echeances = [];
        }

        require_once __DIR__ . '/../view/BackOffice/paiements/liste.php';
    }

    public function detail(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id <= 0) {
            $this->redirectToIndex('', 'Identifiant invalide');
        }

        $paiement = $this->getPaiementById($id);

        if (!$paiement) {
            $this->redirectToIndex('', 'Paiement introuvable');
        }

        require_once __DIR__ . '/../view/BackOffice/paiements/detail.php';
    }

    public function valider(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id <= 0) {
            $this->redirectToIndex('', 'Identifiant invalide');
        }

        $paiement = $this->getPaiementById($id);

        if (!$paiement) {
            $this->redirectToIndex('', 'Paiement introuvable');
        }

        if (($paiement['statut'] ?? '') !== 'en_attente') {
            $this->redirectToIndex('', 'Ce paiement ne peut pas être validé');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = config::getConnexion();

            try {
                $stmt = $db->prepare("
                    UPDATE paiement
                    SET statut = 'valide'
                    WHERE id_paiement = ?
                ");
                $stmt->execute([$id]);

                $this->redirectToIndex('Paiement validé avec succès');
            } catch (Exception $e) {
                $this->redirectToIndex('', 'Erreur : ' . $e->getMessage());
            }
        }

        require_once __DIR__ . '/../view/BackOffice/paiements/valider.php';
    }

    public function refuser(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id <= 0) {
            $this->redirectToIndex('', 'Identifiant invalide');
        }

        $paiement = $this->getPaiementById($id);

        if (!$paiement) {
            $this->redirectToIndex('', 'Paiement introuvable');
        }

        if (($paiement['statut'] ?? '') !== 'en_attente') {
            $this->redirectToIndex('', 'Ce paiement ne peut pas être refusé');
        }

        $erreur = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $motif = trim($_POST['motif'] ?? '');

            if ($motif === '') {
                $erreur = 'Le motif de refus est obligatoire.';
            } else {
                $db = config::getConnexion();

                try {
                    $stmt = $db->prepare("
                        UPDATE paiement
                        SET statut = 'refuse', motif_refus = ?
                        WHERE id_paiement = ?
                    ");
                    $stmt->execute([$motif, $id]);

                    $this->redirectToIndex('Paiement refusé avec succès');
                } catch (Exception $e) {
                    $this->redirectToIndex('', 'Erreur : ' . $e->getMessage());
                }
            }
        }

        require_once __DIR__ . '/../view/BackOffice/paiements/refuser.php';
    }

    public function rembourser(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id <= 0) {
            $this->redirectToIndex('', 'Identifiant invalide');
        }

        $paiement = $this->getPaiementById($id);

        if (!$paiement) {
            $this->redirectToIndex('', 'Paiement introuvable');
        }

        if (($paiement['statut'] ?? '') !== 'valide') {
            $this->redirectToIndex('', 'Seuls les paiements validés peuvent être remboursés');
        }

        $erreur = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $motif = trim($_POST['motif'] ?? '');

            if ($motif === '') {
                $erreur = 'Le motif de remboursement est obligatoire.';
            } else {
                $db = config::getConnexion();

                try {
                    $stmt = $db->prepare("
                        UPDATE paiement
                        SET statut = 'rembourse', motif_refus = ?
                        WHERE id_paiement = ?
                    ");
                    $stmt->execute([$motif, $id]);

                    $this->redirectToIndex('Paiement remboursé avec succès');
                } catch (Exception $e) {
                    $this->redirectToIndex('', 'Erreur : ' . $e->getMessage());
                }
            }
        }

        require_once __DIR__ . '/../view/BackOffice/paiements/rembourser.php';
    }
}

/* ── Routeur ── */
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