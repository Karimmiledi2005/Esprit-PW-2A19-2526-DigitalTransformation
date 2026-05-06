<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) { header('Location: ../view/BackOffice/connexion.html'); exit; }

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
            SELECT p.*, o.nom_offre, o.type_offre, o.description AS offre_description,
                   d.id_devis, d.nom AS client_nom, d.prenom AS client_prenom,
                   d.email AS client_email, d.telephone AS client_tel,
                   d.statut AS devis_statut, d.montant_estime AS devis_montant,
                   d.type_assurance
            FROM paiement p
            LEFT JOIN offre o ON p.id_offre = o.id_offre
            LEFT JOIN devis d ON p.id_devis = d.id_devis
            WHERE p.id_paiement = ?
        ");
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    /**
     * Trouve l'email du client via l'offre liée au devis
     */
    private function getEmailDuClient(PDO $db, int $idOffre): ?string
    {
        try {
            $stmt = $db->prepare("
                SELECT d.email
                FROM devis d
                WHERE d.id_offre = :id_offre
                ORDER BY d.date_demande DESC
                LIMIT 1
            ");
            $stmt->execute([':id_offre' => $idOffre]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['email'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Envoie une notification au client quand un spin est débloqué
     */
    private function envoyerNotificationSpinDebloque(string $email, int $nbSpins): void
    {
        try {
            require_once __DIR__ . '/MailerService.php';
            $mailer = new MailerService();
            $mailer->envoyer(
                $email,
                'Client Protex',
                "🎰 Bravo ! Vous avez débloqué un tour de roulette — Protex",
                "<html><body style='font-family:Arial;background:#f4f6fb;padding:30px;'>
                <div style='max-width:500px;margin:auto;background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 10px 40px rgba(0,0,0,0.08);'>
                    <div style='background:linear-gradient(135deg,#FF6B1A,#ffa050);padding:30px;text-align:center;'>
                        <div style='font-size:50px;'>🎰</div>
                        <h1 style='color:#fff;margin:10px 0;'>Bravo !</h1>
                    </div>
                    <div style='padding:30px;'>
                        <p style='font-size:16px;color:#1a1a1a;'>
                            Vous avez atteint <strong>$nbSpins spin" . ($nbSpins > 1 ? 's' : '') . " de roulette</strong> grâce à vos paiements !
                        </p>
                        <p style='color:#4a5568;font-size:14px;line-height:1.7;'>
                            Rendez-vous sur votre espace client pour tourner la roue et découvrir votre récompense 🎁
                        </p>
                        <a href='http://localhost:8081/projet_web1/gestion_paiment_offre/controller/RouletteController.php?email=" . urlencode($email) . "' 
                           style='display:inline-block;padding:14px 28px;background:linear-gradient(135deg,#FF6B1A,#ff8c42);color:#fff;text-decoration:none;font-weight:700;border-radius:12px;margin-top:10px;'>
                            🎰 Tourner maintenant
                        </a>
                    </div>
                    <div style='background:#0e1c33;padding:20px;text-align:center;color:#64748b;font-size:12px;'>
                        © " . date('Y') . " Protex Assurance
                    </div>
                </div>
                </body></html>"
            );
        } catch (Exception $e) {
            // Silent fail — notification optionnelle
        }
    }

    public function index(): void
    {
        $db      = config::getConnexion();
        $message = $_GET['message'] ?? '';
        $erreur  = $_GET['erreur'] ?? '';

        $dateDebut = $_GET['date_debut'] ?? '';
        $dateFin   = $_GET['date_fin'] ?? '';
        $statut    = $_GET['statut'] ?? '';
        $methode   = $_GET['methode'] ?? '';

        $where   = [];
        $params  = [];

        if ($dateDebut !== '') {
            $where[]  = 'p.date_paiement >= :date_debut';
            $params[':date_debut'] = $dateDebut;
        }
        if ($dateFin !== '') {
            $where[]  = 'p.date_paiement <= :date_fin';
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        }
        if ($statut !== '') {
            $where[]  = 'p.statut = :statut';
            $params[':statut'] = $statut;
        }
        if ($methode !== '') {
            $where[]  = 'p.methode = :methode';
            $params[':methode'] = $methode;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        try {
            $sql = "
                SELECT p.*, o.nom_offre, o.type_offre,
                       d.nom AS client_nom, d.prenom AS client_prenom,
                       d.id_devis, d.statut AS devis_statut
                FROM paiement p
                LEFT JOIN offre o ON p.id_offre = o.id_offre
                LEFT JOIN devis d ON p.id_devis = d.id_devis
                $whereSql
                ORDER BY p.date_paiement DESC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $paiements = $stmt->fetchAll();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }

        try {
            $statsSql = "
                SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) AS en_attente,
                    SUM(CASE WHEN statut = 'valide' THEN 1 ELSE 0 END) AS valides,
                    SUM(CASE WHEN statut = 'refuse' THEN 1 ELSE 0 END) AS refuses,
                    SUM(CASE WHEN statut = 'rembourse' THEN 1 ELSE 0 END) AS rembourses,
                    SUM(CASE WHEN statut = 'valide' THEN montant ELSE 0 END) AS chiffre_affaires,
                    COALESCE(SUM(CASE WHEN MONTH(date_paiement) = MONTH(CURDATE()) AND YEAR(date_paiement) = YEAR(CURDATE()) AND statut = 'valide' THEN montant ELSE 0 END), 0) AS ca_ce_mois,
                    (SELECT COUNT(*) FROM devis WHERE statut = 'accepte') AS devis_acceptes,
                    (SELECT COUNT(*) FROM paiement WHERE id_devis IS NOT NULL) AS paiements_lies_devis
                FROM paiement
            ";
            $stmtStats = $db->query($statsSql);
            $stats = $stmtStats->fetch();
            if (!$stats) { $stats = []; }
        } catch (Exception $e) {
            $stats = [];
        }

        try {
            $revenusMensuels = $db->query("
                SELECT
                    DATE_FORMAT(date_paiement, '%Y-%m') AS mois,
                    SUM(CASE WHEN statut = 'valide' THEN montant ELSE 0 END) AS total_valide,
                    COUNT(CASE WHEN statut = 'valide' THEN 1 END) AS nb_valide
                FROM paiement
                WHERE date_paiement >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(date_paiement, '%Y-%m')
                ORDER BY mois ASC
            ")->fetchAll();
        } catch (Exception $e) {
            $revenusMensuels = [];
        }

        try {
            $statsByType = $db->query("
                SELECT o.type_offre,
                       COUNT(p.id_paiement) AS nb,
                       SUM(CASE WHEN p.statut = 'valide' THEN p.montant ELSE 0 END) AS revenu
                FROM paiement p
                LEFT JOIN offre o ON p.id_offre = o.id_offre
                GROUP BY o.type_offre
                ORDER BY nb DESC
            ")->fetchAll();
        } catch (Exception $e) {
            $statsByType = [];
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

    public function exporter(): void
    {
        $db     = config::getConnexion();
        $format = $_GET['format'] ?? 'csv';

        $dateDebut = $_GET['date_debut'] ?? '';
        $dateFin   = $_GET['date_fin'] ?? '';
        $statut    = $_GET['statut'] ?? '';
        $methode   = $_GET['methode'] ?? '';

        $where  = [];
        $params = [];

        if ($dateDebut !== '') {
            $where[] = 'p.date_paiement >= :date_debut';
            $params[':date_debut'] = $dateDebut;
        }
        if ($dateFin !== '') {
            $where[] = 'p.date_paiement <= :date_fin';
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        }
        if ($statut !== '') {
            $where[] = 'p.statut = :statut';
            $params[':statut'] = $statut;
        }
        if ($methode !== '') {
            $where[] = 'p.methode = :methode';
            $params[':methode'] = $methode;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT p.reference, p.montant, p.methode, p.periodicite, p.statut,
                   p.date_paiement, p.date_echeance, p.motif_refus,
                   o.nom_offre, o.type_offre,
                   d.nom AS client_nom, d.prenom AS client_prenom, d.email AS client_email
            FROM paiement p
            LEFT JOIN offre o ON p.id_offre = o.id_offre
            LEFT JOIN devis d ON p.id_devis = d.id_devis
            $whereSql
            ORDER BY p.date_paiement DESC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ts = date('Y-m-d_H-i');

        if ($format === 'csv') {
            $this->exportCSV($data, $ts);
        } elseif ($format === 'excel') {
            $this->exportExcel($data, $ts);
        } else {
            $this->exportPDF($data, $ts);
        }
    }

    private function exportCSV(array $data, string $ts): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="paiements_protex_' . $ts . '.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Référence', 'Client', 'Email', 'Offre', 'Type', 'Montant (TND)', 'Méthode', 'Périodicité', 'Statut', 'Date paiement', 'Date échéance', 'Motif'], ';');
        foreach ($data as $row) {
            fputcsv($out, [
                $row['reference'] ?? '',
                trim(($row['client_prenom'] ?? '') . ' ' . ($row['client_nom'] ?? '')),
                $row['client_email'] ?? '',
                $row['nom_offre'] ?? '',
                $row['type_offre'] ?? '',
                number_format((float)$row['montant'], 3, '.', ''),
                $row['methode'] ?? '',
                $row['periodicite'] ?? '',
                $row['statut'] ?? '',
                $row['date_paiement'] ?? '',
                $row['date_echeance'] ?? '',
                $row['motif_refus'] ?? '',
            ], ';');
        }
        fclose($out);
        exit;
    }

    private function exportExcel(array $data, string $ts): void
    {
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head><meta charset="UTF-8"><style>
table{border-collapse:collapse;font-family:Arial;font-size:11px;}
th{background:#FF6B1A;color:#fff;font-weight:bold;padding:8px;border:1px solid #999;text-align:center;}
td{padding:6px;border:1px solid #ccc;}
.title{font-size:16px;font-weight:bold;color:#1A3A7A;}
.sub{font-size:10px;color:#666;}
.num{text-align:right;}
.center{text-align:center;}
</style></head><body>
<table>
<tr><td colspan="12" class="title">PROTEX ASSURANCE — Rapport des paiements</td></tr>
<tr><td colspan="12" class="sub">Exporté le ' . date('d/m/Y H:i') . ' — ' . count($data) . ' paiement(s)</td></tr>
<tr><td colspan="12">&nbsp;</td></tr>
<tr>
    <th>Référence</th><th>Client</th><th>Email</th><th>Offre</th><th>Type</th>
    <th>Montant</th><th>Méthode</th><th>Périodicité</th><th>Statut</th>
    <th>Date paiement</th><th>Date échéance</th><th>Motif</th>
</tr>';
        foreach ($data as $row) {
            $client = trim(($row['client_prenom'] ?? '') . ' ' . ($row['client_nom'] ?? ''));
            $html .= '<tr>
                <td class="center">' . htmlspecialchars($row['reference'] ?? '') . '</td>
                <td>' . htmlspecialchars($client) . '</td>
                <td>' . htmlspecialchars($row['client_email'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['nom_offre'] ?? '') . '</td>
                <td class="center">' . htmlspecialchars(ucfirst($row['type_offre'] ?? '')) . '</td>
                <td class="num">' . number_format((float)$row['montant'], 3) . ' TND</td>
                <td class="center">' . htmlspecialchars(ucfirst($row['methode'] ?? '')) . '</td>
                <td class="center">' . htmlspecialchars(ucfirst($row['periodicite'] ?? '')) . '</td>
                <td class="center">' . htmlspecialchars(ucfirst($row['statut'] ?? '')) . '</td>
                <td class="center">' . htmlspecialchars($row['date_paiement'] ?? '') . '</td>
                <td class="center">' . htmlspecialchars($row['date_echeance'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['motif_refus'] ?? '') . '</td>
            </tr>';
        }
        $html .= '</table></body></html>';
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="paiements_protex_' . $ts . '.xls"');
        echo "\xEF\xBB\xBF" . $html;
        exit;
    }

    private function exportPDF(array $data, string $ts): void
    {
        $rowsHtml = '';
        foreach ($data as $row) {
            $client = trim(($row['client_prenom'] ?? '') . ' ' . ($row['client_nom'] ?? ''));
            $rowsHtml .= '<tr>
                <td>' . htmlspecialchars($row['reference'] ?? '') . '</td>
                <td>' . htmlspecialchars($client) . '</td>
                <td>' . htmlspecialchars(ucfirst($row['type_offre'] ?? '')) . '</td>
                <td style="text-align:right">' . number_format((float)$row['montant'], 3) . ' TND</td>
                <td>' . htmlspecialchars(ucfirst($row['methode'] ?? '')) . '</td>
                <td>' . htmlspecialchars(ucfirst($row['statut'] ?? '')) . '</td>
                <td>' . htmlspecialchars($row['date_paiement'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['date_echeance'] ?? '') . '</td>
            </tr>';
        }
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
            body{font-family:Arial,sans-serif;padding:20px;color:#000;}
            .print-header{border-bottom:3px solid #FF6B1A;padding-bottom:12px;margin-bottom:18px;}
            .print-brand{font-size:22px;font-weight:800;color:#1A3A7A;margin:0;}
            .print-brand span{color:#FF6B1A;}
            .print-info{color:#666;font-size:11px;margin-top:4px;}
            table{width:100%;border-collapse:collapse;font-size:10px;}
            th,td{border:1px solid #999;padding:6px 8px;}
            th{background:#FF6B1A;color:#fff;-webkit-print-color-adjust:exact;print-color-adjust:exact;text-align:left;}
        </style></head><body>
        <div class="print-header">
            <h1 class="print-brand">PROTEX <span>Assurance</span></h1>
            <p class="print-info">Rapport des paiements — Export du ' . date('d/m/Y H:i') . ' — ' . count($data) . ' paiement(s)</p>
        </div>
        <table><thead><tr>
            <th>Référence</th><th>Client</th><th>Type</th><th>Montant</th>
            <th>Méthode</th><th>Statut</th><th>Date paiement</th><th>Échéance</th>
        </tr></thead><tbody>' . $rowsHtml . '</tbody></table>
        <p style="margin-top:20px;font-size:9px;color:#888;text-align:center;">
            Document généré automatiquement par Protex Admin — ' . date('d/m/Y') . '
        </p></body></html>';
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="paiements_protex_' . $ts . '.html"');
        echo $html;
        exit;
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
                $db->beginTransaction();

                $stmt = $db->prepare("
                    UPDATE paiement
                    SET statut = 'valide'
                    WHERE id_paiement = ?
                ");
                $stmt->execute([$id]);

                // 🎟️ Marquer le code promo comme utilisé s'il y en a un
                $stmtPromo = $db->prepare("SELECT code_promo FROM paiement WHERE id_paiement = ?");
                $stmtPromo->execute([$id]);
                $rowPromo = $stmtPromo->fetch(PDO::FETCH_ASSOC);
                if ($rowPromo && !empty($rowPromo['code_promo'])) {
                    require_once __DIR__ . '/../model/Roulette.php';
                    Roulette::marquerCodeUtilise($db, $rowPromo['code_promo']);
                }

                // 🎰 Vérifier si le client débloque un spin de roulette
                $emailClient = $this->getEmailDuClient($db, (int)$paiement['id_offre']);
                if ($emailClient) {
                    require_once __DIR__ . '/../model/Roulette.php';
                    $nbPaiements = Roulette::compterPaiementsValides($db, $emailClient);
                    if ($nbPaiements > 0 && $nbPaiements % Roulette::SEUIL_PAR_SPIN === 0) {
                        $nbSpins = $nbPaiements / Roulette::SEUIL_PAR_SPIN;
                        $this->envoyerNotificationSpinDebloque($emailClient, $nbSpins);
                    }
                }

                $db->commit();

                $this->redirectToIndex('Paiement validé avec succès');
            } catch (Exception $e) {
                $db->rollBack();
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

    case 'exporter':
        $controller->exporter();
        break;

    default:
        $controller->index();
        break;
}