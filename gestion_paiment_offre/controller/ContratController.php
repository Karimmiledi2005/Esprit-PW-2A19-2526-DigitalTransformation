<?php
/**
 * controller/ContratController.php
 * Gestion des contrats — BackOffice Protex 2026
 */

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) { header('Location: ../view/BackOffice/connexion.html'); exit; }

include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../model/Contrat.php';
include_once __DIR__ . '/../model/Devis.php';

if (session_status() === PHP_SESSION_NONE) session_start();

class ContratController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    public function index(): void
    {
        $search = trim($_GET['search'] ?? '');
        $statut = trim($_GET['statut'] ?? '');

        $filters = [];
        if ($search !== '') $filters['search'] = $search;
        if ($statut !== '') $filters['statut'] = $statut;

        $contrats = Contrat::getAll($this->db, $filters);
        $stats = Contrat::getStats($this->db);

        require_once __DIR__ . '/../view/BackOffice/contrats/liste.php';
    }

    public function details(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: ContratController.php?action=index');
            exit;
        }

        $contrat = Contrat::getById($this->db, $id);
        if (!$contrat) {
            header('Location: ContratController.php?action=index&erreur=Contrat+introuvable');
            exit;
        }

        require_once __DIR__ . '/../view/BackOffice/contrats/detail.php';
    }

    public function changerStatut(): void
    {
        header('Content-Type: application/json');

        $id = (int)($_POST['id_contrat'] ?? 0);
        $statut = trim($_POST['statut'] ?? '');

        if ($id <= 0 || !in_array($statut, ['actif', 'expire', 'resilie', 'en_attente'], true)) {
            echo json_encode(['error' => 'Données invalides']);
            return;
        }

        try {
            Contrat::updateStatut($this->db, $id, $statut);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function genererPDF(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: ContratController.php?action=index');
            exit;
        }

        $contrat = Contrat::getById($this->db, $id);
        if (!$contrat) {
            header('Location: ContratController.php?action=index&erreur=Contrat+introuvable');
            exit;
        }

        require_once __DIR__ . '/../view/BackOffice/contrats/generer_pdf.php';
    }
}

$controller = new ContratController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'details':        $controller->details();        break;
    case 'changer_statut': $controller->changerStatut(); break;
    case 'generer_pdf':    $controller->genererPDF();    break;
    default:               $controller->index();         break;
}
