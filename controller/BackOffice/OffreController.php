<?php
/* =============================================
   OffreController.php — BackOffice
   Protex Assurance
   ============================================= */

require_once __DIR__ . '/../../model/OffreModel.php';

class OffreController
{
    private OffreModel $model;
    private string     $baseUrl;

    public function __construct()
    {
        $this->model   = new OffreModel();
        $this->baseUrl = '/projet_web/MVC TEMPLATE/controller/BackOffice/OffreController.php';
    }

    /* =============================================
       Helper — Redirection vers index
       ============================================= */
    private function redirectToIndex(string $message = '', string $erreur = ''): void
    {
        $params = ['action' => 'index'];
        if ($message !== '') $params['message'] = $message;
        if ($erreur  !== '') $params['erreur']  = $erreur;
        header('Location: ' . $this->baseUrl . '?' . http_build_query($params));
        exit;
    }

    /* =============================================
       index() — Lister toutes les offres
       ============================================= */
    public function index(): void
    {
        $offres  = $this->model->getAll();
        $stats   = $this->model->getStats();
        $message = $_GET['message'] ?? '';
        $erreur  = $_GET['erreur']  ?? '';

        require_once __DIR__ . '/../../view/BackOffice/offres/liste.php';
    }

    /* =============================================
       ajouter() — Afficher + traiter formulaire
       ============================================= */
    public function ajouter(): void
    {
        $errors = [];
        $old    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old    = $this->sanitizePost($_POST);
            $errors = $this->valider($_POST);

            if (empty($errors)) {
                $data = $this->buildData($_POST);

                if ($this->model->ajouter($data)) {
                    $this->redirectToIndex('Offre ajoutée avec succès');
                } else {
                    $errors['general'] = 'Erreur lors de l\'ajout. Veuillez réessayer.';
                }
            }
        }

        require_once __DIR__ . '/../../view/BackOffice/offres/ajouter.php';
    }

    /* =============================================
       modifier() — Afficher + traiter formulaire
       ============================================= */
    public function modifier(): void
    {
        $id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $offre  = null;
        $errors = [];
        $old    = [];

        if ($id <= 0) {
            $this->redirectToIndex('', 'Identifiant invalide');
        }

        $offre = $this->model->getById($id);

        if (!$offre) {
            $this->redirectToIndex('', 'Offre introuvable');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old    = $this->sanitizePost($_POST);
            $errors = $this->valider($_POST);

            if (empty($errors)) {
                $data = $this->buildData($_POST);

                if ($this->model->modifier($id, $data)) {
                    $this->redirectToIndex('Offre modifiée avec succès');
                } else {
                    $errors['general'] = 'Erreur lors de la modification.';
                }
            }
        }

        require_once __DIR__ . '/../../view/BackOffice/offres/modifier.php';
    }

    /* =============================================
       supprimer() — Supprimer une offre
       ============================================= */
    public function supprimer(): void
    {
        $id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $offre = null;

        if ($id <= 0) {
            $this->redirectToIndex('', 'Identifiant invalide');
        }

        $offre = $this->model->getById($id);

        if (!$offre) {
            $this->redirectToIndex('', 'Offre introuvable');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->supprimer($id)) {
                $this->redirectToIndex('Offre supprimée avec succès');
            } else {
                $this->redirectToIndex('', 'Erreur lors de la suppression');
            }
        }

        require_once __DIR__ . '/../../view/BackOffice/offres/supprimer.php';
    }

    /* =============================================
       changerStatut() — Activer / Suspendre / Archiver
       ============================================= */
    public function changerStatut(): void
    {
        $id     = isset($_GET['id'])     ? (int)$_GET['id']              : 0;
        $statut = isset($_GET['statut']) ? trim((string)$_GET['statut']) : '';

        $statuts_valides = ['active', 'suspendue', 'archivee'];

        if ($id <= 0) {
            $this->redirectToIndex('', 'Identifiant invalide');
        }

        if (!in_array($statut, $statuts_valides, true)) {
            $this->redirectToIndex('', 'Statut invalide');
        }

        if (!$this->model->getById($id)) {
            $this->redirectToIndex('', 'Offre introuvable');
        }

        if ($this->model->changerStatut($id, $statut)) {
            $labels = [
                'active'    => 'activée',
                'suspendue' => 'suspendue',
                'archivee'  => 'archivée',
            ];
            $this->redirectToIndex('Offre ' . ($labels[$statut] ?? 'modifiée') . ' avec succès');
        } else {
            $this->redirectToIndex('', 'Erreur lors du changement de statut');
        }
    }

    /* =============================================
       valider() — Validation complète formulaire
       ============================================= */
    private function valider(array $data): array
    {
        $errors = [];

        $nom_offre    = trim((string)($data['nom_offre']    ?? ''));
        $type_offre   = trim((string)($data['type_offre']   ?? ''));
        $description  = trim((string)($data['description']  ?? ''));
        $couverture   = trim((string)($data['couverture']   ?? ''));
        $prix_mensuel = trim((string)($data['prix_mensuel'] ?? ''));
        $prix_annuel  = trim((string)($data['prix_annuel']  ?? ''));
        $plafond      = trim((string)($data['plafond']      ?? ''));
        $duree_min    = trim((string)($data['duree_min']    ?? ''));
        $statut       = trim((string)($data['statut']       ?? ''));

        /* ══════════════════════════════════════
           NOM DE L'OFFRE
           - Obligatoire
           - Min 3 / Max 100 caractères
           - Pas de chiffres
           - Lettres, espaces, tirets, apostrophes
        ══════════════════════════════════════ */
        if ($nom_offre === '') {
            $errors['nom_offre'] = 'Le nom de l\'offre est obligatoire.';
        } elseif (mb_strlen($nom_offre) < 3) {
            $errors['nom_offre'] = 'Le nom doit contenir au moins 3 caractères.';
        } elseif (mb_strlen($nom_offre) > 100) {
            $errors['nom_offre'] = 'Le nom ne peut pas dépasser 100 caractères.';
        } elseif (preg_match('/[0-9]/', $nom_offre)) {
            $errors['nom_offre'] = 'Le nom ne doit pas contenir de chiffres.';
        } elseif (!preg_match('/^[\p{L}\s\-\'\.]+$/u', $nom_offre)) {
            $errors['nom_offre'] = 'Le nom ne doit contenir que des lettres, espaces, tirets ou apostrophes.';
        }

        /* ══════════════════════════════════════
           TYPE
           - Obligatoire
           - Valeurs autorisées uniquement
        ══════════════════════════════════════ */
        $types_valides = ['auto', 'sante', 'habitation', 'vie'];
        if ($type_offre === '') {
            $errors['type_offre'] = 'Le type est obligatoire.';
        } elseif (!in_array($type_offre, $types_valides, true)) {
            $errors['type_offre'] = 'Le type sélectionné est invalide.';
        }

        /* ══════════════════════════════════════
           DESCRIPTION
           - Obligatoire
           - Min 10 / Max 1000 caractères
           - Pas que des espaces
           - Pas que des chiffres
        ══════════════════════════════════════ */
        if ($description === '') {
            $errors['description'] = 'La description est obligatoire.';
        } elseif (mb_strlen($description) < 10) {
            $errors['description'] = 'La description doit contenir au moins 10 caractères.';
        } elseif (mb_strlen($description) > 1000) {
            $errors['description'] = 'La description ne peut pas dépasser 1000 caractères.';
        } elseif (preg_match('/^\d+$/', $description)) {
            $errors['description'] = 'La description ne peut pas contenir uniquement des chiffres.';
        } elseif (trim(strip_tags($description)) === '') {
            $errors['description'] = 'La description ne peut pas être vide.';
        }

        /* ══════════════════════════════════════
           COUVERTURE
           - Obligatoire
           - Min 5 / Max 500 caractères
           - Pas que des chiffres
           - Doit contenir des lettres
        ══════════════════════════════════════ */
        if ($couverture === '') {
            $errors['couverture'] = 'La couverture est obligatoire.';
        } elseif (mb_strlen($couverture) < 5) {
            $errors['couverture'] = 'La couverture doit contenir au moins 5 caractères.';
        } elseif (mb_strlen($couverture) > 500) {
            $errors['couverture'] = 'La couverture ne peut pas dépasser 500 caractères.';
        } elseif (preg_match('/^\d+$/', $couverture)) {
            $errors['couverture'] = 'La couverture ne peut pas contenir uniquement des chiffres.';
        } elseif (!preg_match('/\p{L}/u', $couverture)) {
            $errors['couverture'] = 'La couverture doit contenir au moins une lettre.';
        }

        /* ══════════════════════════════════════
           PRIX MENSUEL
           - Obligatoire
           - Nombre décimal positif
           - Max 99 999.999 TND
        ══════════════════════════════════════ */
        if ($prix_mensuel === '') {
            $errors['prix_mensuel'] = 'Le prix mensuel est obligatoire.';
        } elseif (!is_numeric($prix_mensuel)) {
            $errors['prix_mensuel'] = 'Le prix mensuel doit être un nombre valide.';
        } elseif ((float)$prix_mensuel <= 0) {
            $errors['prix_mensuel'] = 'Le prix mensuel doit être supérieur à 0.';
        } elseif ((float)$prix_mensuel > 99999.999) {
            $errors['prix_mensuel'] = 'Le prix mensuel ne peut pas dépasser 99 999 TND.';
        }

        /* ══════════════════════════════════════
           PRIX ANNUEL
           - Obligatoire
           - Nombre décimal positif
           - Max 999 999.999 TND
        ══════════════════════════════════════ */
        if ($prix_annuel === '') {
            $errors['prix_annuel'] = 'Le prix annuel est obligatoire.';
        } elseif (!is_numeric($prix_annuel)) {
            $errors['prix_annuel'] = 'Le prix annuel doit être un nombre valide.';
        } elseif ((float)$prix_annuel <= 0) {
            $errors['prix_annuel'] = 'Le prix annuel doit être supérieur à 0.';
        } elseif ((float)$prix_annuel > 999999.999) {
            $errors['prix_annuel'] = 'Le prix annuel ne peut pas dépasser 999 999 TND.';
        }

        /* ══════════════════════════════════════
           COHÉRENCE PRIX MENSUEL / ANNUEL
        ══════════════════════════════════════ */
        if (
            empty($errors['prix_mensuel']) &&
            empty($errors['prix_annuel'])  &&
            is_numeric($prix_mensuel)      &&
            is_numeric($prix_annuel)
        ) {
            $m   = (float)$prix_mensuel;
            $a   = (float)$prix_annuel;
            $m12 = $m * 12;

            if ($a >= $m12) {
                $errors['prix_annuel'] =
                    'Le prix annuel (' . number_format($a, 3) . ' TND) doit être '
                    . 'inférieur à prix mensuel × 12 (' . number_format($m12, 3) . ' TND).';
            } elseif ($a < $m) {
                $errors['prix_annuel'] =
                    'Le prix annuel ne peut pas être inférieur au prix mensuel ('
                    . number_format($m, 3) . ' TND).';
            }
        }

        /* ══════════════════════════════════════
           PLAFOND (optionnel)
           - Si renseigné → nombre positif
           - Max 9 999 999 TND
        ══════════════════════════════════════ */
        if ($plafond !== '') {
            if (!is_numeric($plafond)) {
                $errors['plafond'] = 'Le plafond doit être un nombre valide.';
            } elseif ((float)$plafond <= 0) {
                $errors['plafond'] = 'Le plafond doit être supérieur à 0.';
            } elseif ((float)$plafond > 9999999.999) {
                $errors['plafond'] = 'Le plafond ne peut pas dépasser 9 999 999 TND.';
            }
        }

        /* ══════════════════════════════════════
           DURÉE MINIMALE
           - Obligatoire
           - Entier positif
           - Entre 1 et 120 mois
        ══════════════════════════════════════ */
        if ($duree_min === '') {
            $errors['duree_min'] = 'La durée minimale est obligatoire.';
        } elseif (!ctype_digit($duree_min)) {
            $errors['duree_min'] = 'La durée minimale doit être un nombre entier positif.';
        } elseif ((int)$duree_min < 1) {
            $errors['duree_min'] = 'La durée minimale doit être au moins 1 mois.';
        } elseif ((int)$duree_min > 120) {
            $errors['duree_min'] = 'La durée minimale ne peut pas dépasser 120 mois (10 ans).';
        }

        /* ══════════════════════════════════════
           STATUT
           - Valeurs autorisées uniquement
        ══════════════════════════════════════ */
        $statuts_valides = ['active', 'suspendue', 'archivee'];
        if ($statut !== '' && !in_array($statut, $statuts_valides, true)) {
            $errors['statut'] = 'Le statut sélectionné est invalide.';
        }

        return $errors;
    }

    /* =============================================
       sanitizePost() — Nettoyer les données POST
       ============================================= */
    private function sanitizePost(array $post): array
    {
        return array_map(
            fn($v) => htmlspecialchars(trim((string)($v ?? '')), ENT_QUOTES, 'UTF-8'),
            $post
        );
    }

    /* =============================================
       buildData() — Construire le tableau de données
       ============================================= */
    private function buildData(array $post): array
    {
        return [
            'nom_offre'    => trim((string)$post['nom_offre']),
            'type_offre'   => trim((string)$post['type_offre']),
            'description'  => trim((string)$post['description']),
            'prix_mensuel' => round((float)$post['prix_mensuel'], 3),
            'prix_annuel'  => round((float)$post['prix_annuel'],  3),
            'couverture'   => trim((string)$post['couverture']),
            'plafond'      => (isset($post['plafond']) && $post['plafond'] !== '')
                                ? round((float)$post['plafond'], 3)
                                : null,
            'duree_min'    => (int)$post['duree_min'],
            'statut'       => in_array($post['statut'] ?? '', ['active','suspendue','archivee'])
                                ? $post['statut']
                                : 'active',
        ];
    }
}

/* =============================================
   Routeur simple — action GET
   ============================================= */
$controller = new OffreController();
$action     = $_GET['action'] ?? 'index';

switch ($action) {
    case 'ajouter':   $controller->ajouter();       break;
    case 'modifier':  $controller->modifier();      break;
    case 'supprimer': $controller->supprimer();     break;
    case 'statut':    $controller->changerStatut(); break;
    default:          $controller->index();         break;
}