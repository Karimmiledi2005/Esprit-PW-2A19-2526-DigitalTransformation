<?php
/* =============================================
   PaiementController.php — FrontOffice
   Protex Assurance
   ============================================= */

require_once __DIR__ . '/../../model/OffreModel.php';
require_once __DIR__ . '/../../model/PaiementModel.php';

class PaiementController {

    private OffreModel   $offreModel;
    private PaiementModel $paiementModel;

    public function __construct() {
        $this->offreModel    = new OffreModel();
        $this->paiementModel = new PaiementModel();
    }

    /* =============================================
       index() — Afficher formulaire + traitement
       ============================================= */
    public function index(): void {

        /* Récupérer offre depuis GET */
        $offreId = isset($_GET['offre']) ? (int)$_GET['offre'] : 1;
        $offre   = $this->offreModel->getById($offreId);

        /* Si offre inexistante → prendre la première */
        if (!$offre || $offre['statut'] !== 'active') {
            $offres  = $this->offreModel->getActives();
            $offre   = $offres[0] ?? null;
            $offreId = $offre['id_offre'] ?? 1;
        }

        $errors  = [];
        $success = [];
        $old     = [];

        /* ── Traitement POST ── */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            /* Sanitize */
            $old = array_map(
                fn($v) => htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8'),
                $_POST
            );

            /* Periodicite + montant */
            $periodicite = $_POST['periodicite'] ?? 'mensuel';
            $montant     = ($periodicite === 'annuel')
                ? $offre['prix_annuel']
                : $offre['prix_mensuel'];

            /* ── Validation ── */
            $errors = $this->valider($_POST);

            /* ── Si pas d'erreurs → enregistrer ── */
            if (empty($errors)) {

                $data = [
                    'id_offre'    => $offreId,
                    'montant'     => $montant,
                    'methode'     => $_POST['methode']     ?? 'carte',
                    'periodicite' => $periodicite,
                    'num_carte'   => $_POST['cardnumber']  ?? '',
                ];

                $result = $this->paiementModel->creer($data);

                if ($result) {
                    /* Récupérer le dernier paiement créé */
                    $dernierPaiement = $this->paiementModel
                        ->getByReference($this->getDerniereReference());

                    $success = [
                        'reference' => $dernierPaiement['reference']  ?? 'PTX-XXXXXXXX',
                        'offre'     => $offre['nom_offre'],
                        'montant'   => $montant . ' TND / ' . $periodicite,
                        'date'      => date('d/m/Y à H:i'),
                        'titulaire' => htmlspecialchars(trim($_POST['fullname'])),
                        'echeance'  => ($periodicite === 'annuel')
                                        ? date('d/m/Y', strtotime('+1 year'))
                                        : date('d/m/Y', strtotime('+1 month')),
                    ];
                } else {
                    $errors['general'] = 'Une erreur est survenue. Veuillez réessayer.';
                }
            }
        }

        /* Passer les données à la vue */
        require_once __DIR__ . '/../../view/FrontOffice/paiement.php';
    }

    /* =============================================
       valider() — Validation des champs POST
       ============================================= */
    private function valider(array $data): array {

        $errors = [];

        /* Champs obligatoires */
        $required = [
            'fullname'   => 'Nom complet',
            'email'      => 'Adresse e-mail',
            'phone'      => 'Téléphone',
            'cardnumber' => 'Numéro de carte',
            'cardholder' => 'Titulaire',
            'expiry'     => 'Expiration',
            'cvv'        => 'CVV',
            'address'    => 'Adresse de facturation',
        ];

        foreach ($required as $field => $label) {
            if (empty(trim($data[$field] ?? ''))) {
                $errors[$field] = "Le champ « {$label} » est obligatoire.";
            }
        }

        /* Email */
        if (!empty($data['email']) &&
            !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse e-mail invalide.';
        }

        /* Carte 16 chiffres */
        if (!empty($data['cardnumber'])) {
            $card = preg_replace('/\s+/', '', $data['cardnumber']);
            if (!preg_match('/^\d{16}$/', $card)) {
                $errors['cardnumber'] = 'Le numéro de carte doit contenir 16 chiffres.';
            }
        }

        /* Expiration MM/AA */
        if (!empty($data['expiry'])) {
            if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $data['expiry'])) {
                $errors['expiry'] = 'Format attendu : MM/AA.';
            } else {
                [$m, $y] = explode('/', $data['expiry']);
                if (DateTime::createFromFormat('m/y', "{$m}/{$y}") < new DateTime()) {
                    $errors['expiry'] = 'Cette carte est expirée.';
                }
            }
        }

        /* CVV */
        if (!empty($data['cvv']) &&
            !preg_match('/^\d{3,4}$/', $data['cvv'])) {
            $errors['cvv'] = 'Le CVV doit contenir 3 ou 4 chiffres.';
        }

        /* Téléphone */
        if (!empty($data['phone'])) {
            $phone = preg_replace('/[\s\+\-\(\)]/', '', $data['phone']);
            if (!preg_match('/^\d{8,15}$/', $phone)) {
                $errors['phone'] = 'Numéro de téléphone invalide.';
            }
        }

        return $errors;
    }

    /* =============================================
       getDerniereReference() — Dernière référence
       ============================================= */
    private function getDerniereReference(): string {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT reference FROM paiement
            ORDER BY id_paiement DESC
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetchColumn() ?? '';
    }
}

/* =============================================
   Point d'entrée
   ============================================= */
$controller = new PaiementController();
$controller->index();