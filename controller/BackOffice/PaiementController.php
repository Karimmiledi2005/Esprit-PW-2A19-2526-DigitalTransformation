<?php
/* =============================================
   PaiementController.php — BackOffice
   Protex Assurance
   ============================================= */

require_once __DIR__ . '/../../model/PaiementModel.php';
require_once __DIR__ . '/../../model/OffreModel.php';

class PaiementController {

    private PaiementModel $model;
    private OffreModel    $offreModel;
    private string        $baseUrl;

    public function __construct() {
        $this->model      = new PaiementModel();
        $this->offreModel = new OffreModel();
        $this->baseUrl    = '/projet_web/MVC TEMPLATE/controller/BackOffice/PaiementController.php';
    }

    /* ── Helper redirection ── */
    private function redirectToIndex(string $message = '', string $erreur = ''): void {
        $params = ['action' => 'index'];
        if ($message !== '') $params['message'] = $message;
        if ($erreur  !== '') $params['erreur']  = $erreur;
        header('Location: ' . $this->baseUrl . '?' . http_build_query($params));
        exit;
    }

    /* =============================================
       index() — Lister tous les paiements
       ============================================= */
    public function index(): void {

        $filtre  = $_GET['statut'] ?? 'tous';
        $message = $_GET['message'] ?? '';
        $erreur  = $_GET['erreur']  ?? '';

        if ($filtre === 'tous') {
            $paiements = $this->model->getAll();
        } else {
            $paiements = $this->model->getByStatut($filtre);
        }

        $stats     = $this->model->getStats();
        $echeances = $this->model->getEcheancesProches();

        require_once __DIR__ . '/../../view/BackOffice/paiements/liste.php';
    }

    /* =============================================
       detail() — Détail d'un paiement
       ============================================= */
    public function detail(): void {

        $id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $paiement = $this->model->getById($id);

        if (!$paiement) {
            $this->redirectToIndex('', 'Paiement introuvable');
        }

        require_once __DIR__ . '/../../view/BackOffice/paiements/detail.php';
    }

    /* =============================================
       valider() — Valider un paiement
       ============================================= */
    public function valider(): void {

        $id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $paiement = $this->model->getById($id);

        if (!$paiement) {
            $this->redirectToIndex('', 'Paiement introuvable');
        }

        if ($paiement['statut'] !== 'en_attente') {
            $this->redirectToIndex('', 'Ce paiement ne peut pas être validé');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->valider($id)) {
                $this->redirectToIndex('Paiement validé avec succès');
            } else {
                $this->redirectToIndex('', 'Erreur lors de la validation');
            }
        }

        $action = 'valider';
        require_once __DIR__ . '/../../view/BackOffice/paiements/statut.php';
    }

    /* =============================================
       refuser() — Refuser un paiement
       ============================================= */
    public function refuser(): void {

        $id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $paiement = $this->model->getById($id);

        if (!$paiement) {
            $this->redirectToIndex('', 'Paiement introuvable');
        }

        if ($paiement['statut'] !== 'en_attente') {
            $this->redirectToIndex('', 'Ce paiement ne peut pas être refusé');
        }

        $erreur = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $motif = trim($_POST['motif'] ?? '');

            if (empty($motif)) {
                $erreur = 'Le motif de refus est obligatoire.';
            } else {
                if ($this->model->refuser($id, $motif)) {
                    $this->redirectToIndex('Paiement refusé avec succès');
                } else {
                    $this->redirectToIndex('', 'Erreur lors du refus');
                }
            }
        }

        $action = 'refuser';
        require_once __DIR__ . '/../../view/BackOffice/paiements/statut.php';
    }

    /* =============================================
       rembourser() — Rembourser un paiement
       ============================================= */
    public function rembourser(): void {

        $id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $paiement = $this->model->getById($id);

        if (!$paiement) {
            $this->redirectToIndex('', 'Paiement introuvable');
        }

        if ($paiement['statut'] !== 'valide') {
            $this->redirectToIndex('', 'Seuls les paiements validés peuvent être remboursés');
        }

        $erreur = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $motif = trim($_POST['motif'] ?? '');

            if (empty($motif)) {
                $erreur = 'Le motif de remboursement est obligatoire.';
            } else {
                if ($this->model->rembourser($id, $motif)) {
                    $this->redirectToIndex('Paiement remboursé avec succès');
                } else {
                    $this->redirectToIndex('', 'Erreur lors du remboursement');
                }
            }
        }

        $action = 'rembourser';
        require_once __DIR__ . '/../../view/BackOffice/paiements/statut.php';
    }

    /* =============================================
       stats() — Statistiques paiements
       ============================================= */
    public function stats(): void {

        $stats     = $this->model->getStats();
        $echeances = $this->model->getEcheancesProches();
        $offres    = $this->offreModel->getAll();

        require_once __DIR__ . '/../../view/BackOffice/paiements/stats.php';
    }
}

/* =============================================
   Routeur simple — action GET
   ============================================= */
$controller = new PaiementController();
$action     = $_GET['action'] ?? 'index';

switch ($action) {
    case 'detail':     $controller->detail();     break;
    case 'valider':    $controller->valider();    break;
    case 'refuser':    $controller->refuser();    break;
    case 'rembourser': $controller->rembourser(); break;
    case 'stats':      $controller->stats();      break;
    default:           $controller->index();      break;
}