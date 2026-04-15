<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../model/Paiement.php');
include(__DIR__ . '/../model/Offre.php');

class PaiementController
{
    private string $baseUrl = '/projet_web/MVC TEMPLATE/controller/PaiementController.php';

    private function redirectToIndex(string $message = '', string $erreur = ''): void
    {
        $params = ['action' => 'index'];
        if ($message !== '') $params['message'] = $message;
        if ($erreur  !== '') $params['erreur']  = $erreur;
        header('Location: ' . $this->baseUrl . '?' . http_build_query($params));
        exit;
    }

    public function index(): void
    {
        $db      = config::getConnexion();
        $message = $_GET['message'] ?? '';
        $erreur  = $_GET['erreur']  ?? '';

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
                    COUNT(*)                        AS total,
                    SUM(statut='en_attente')        AS en_attente,
                    SUM(statut='valide')            AS valides,
                    SUM(statut='refuse')            AS refuses,
                    SUM(statut='rembourse')         AS rembourses,
                    SUM(CASE WHEN statut='valide' THEN montant ELSE 0 END) AS chiffre_affaires
                FROM paiement
            ")->fetch() ?: [];
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
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) $this->redirectToIndex('', 'Identifiant invalide');

        $db   = config::getConnexion();
        $stmt = $db->prepare("
            SELECT p.*, o.nom_offre, o.type_offre, o.description AS offre_description
            FROM paiement p
            LEFT JOIN offre o ON p.id_offre = o.id_offre
            WHERE p.id_paiement = ?
        ");
        $stmt->execute([$id]);
        $paiement = $stmt->fetch();

        if (!$paiement) $this->redirectToIndex('', 'Paiement introuvable');

        require_once __DIR__ . '/../view/BackOffice/paiements/detail.php';
    }

    public function valider(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) $this->redirectToIndex('', 'Identifiant invalide');

        $db   = config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM paiement WHERE id_paiement = ?");
        $stmt->execute([$id]);
        $paiement = $stmt->fetch();

        if (!$paiement)                           $this->redirectToIndex('', 'Paiement introuvable');
        if ($paiement['statut'] !== 'en_attente') $this->redirectToIndex('', 'Ce paiement ne peut pas être validé');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $db->prepare("UPDATE paiement SET statut = 'valide' WHERE id_paiement = ?")
                   ->execute([$id]);
                $this->redirectToIndex('Paiement validé avec succès');
            } catch (Exception $e) {
                $this->redirectToIndex('', 'Erreur : ' . $e->getMessage());
            }
        }

        $action = 'valider';
        require_once __DIR__ . '/../view/BackOffice/paiements/statut.php';
    }

    public function refuser(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) $this->redirectToIndex('', 'Identifiant invalide');

        $db   = config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM paiement WHERE id_paiement = ?");
        $stmt->execute([$id]);
        $paiement = $stmt->fetch();

        if (!$paiement)                           $this->redirectToIndex('', 'Paiement introuvable');
        if ($paiement['statut'] !== 'en_attente') $this->redirectToIndex('', 'Ce paiement ne peut pas être refusé');

        $erreur = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $motif = trim($_POST['motif'] ?? '');
            if (empty($motif)) {
                $erreur = 'Le motif de refus est obligatoire.';
            } else {
                try {
                    $db->prepare("UPDATE paiement SET statut = 'refuse', motif_refus = ? WHERE id_paiement = ?")
                       ->execute([$motif, $id]);
                    $this->redirectToIndex('Paiement refusé avec succès');
                } catch (Exception $e) {
                    $this->redirectToIndex('', 'Erreur : ' . $e->getMessage());
                }
            }
        }

        $action = 'refuser';
        require_once __DIR__ . '/../view/BackOffice/paiements/statut.php';
    }

    public function rembourser(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) $this->redirectToIndex('', 'Identifiant invalide');

        $db   = config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM paiement WHERE id_paiement = ?");
        $stmt->execute([$id]);
        $paiement = $stmt->fetch();

        if (!$paiement)                       $this->redirectToIndex('', 'Paiement introuvable');
        if ($paiement['statut'] !== 'valide') $this->redirectToIndex('', 'Seuls les paiements validés peuvent être remboursés');

        $erreur = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $motif = trim($_POST['motif'] ?? '');
            if (empty($motif)) {
                $erreur = 'Le motif de remboursement est obligatoire.';
            } else {
                try {
                    $db->prepare("UPDATE paiement SET statut = 'rembourse', motif_refus = ? WHERE id_paiement = ?")
                       ->execute([$motif, $id]);
                    $this->redirectToIndex('Paiement remboursé avec succès');
                } catch (Exception $e) {
                    $this->redirectToIndex('', 'Erreur : ' . $e->getMessage());
                }
            }
        }

        $action = 'rembourser';
        require_once __DIR__ . '/../view/BackOffice/paiements/statut.php';
    }
}

/* ── Routeur ── */
$controller = new PaiementController();
$action     = $_GET['action'] ?? 'index';

switch ($action) {
    case 'detail':     $controller->detail();     break;
    case 'valider':    $controller->valider();    break;
    case 'refuser':    $controller->refuser();    break;
    case 'rembourser': $controller->rembourser(); break;
    default:           $controller->index();      break;
}