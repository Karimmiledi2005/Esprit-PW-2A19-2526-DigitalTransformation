<?php
session_start();
require_once __DIR__ . '/../../controller/ContratController.php';
require_once __DIR__ . '/../../model/Contrat.php';

function normalizeType(string $type): string {
    $type = strtolower(trim($type));
    $type = str_replace(['é','è','ê','à'], ['e','e','e','a'], $type);
    return $type;
}

function backToType(string $type): string {
    return match (normalizeType($type)) {
        'auto' => 'contrat_auto.php',
        'habitation' => 'contrat_habitation.php',
        'sante' => 'contrat_sante.php',
        'protection' => 'contrat_protection.php',
        default => 'contrat.php',
    };
}

function redirectWith(string $url, string $msg): void {
    header('Location: ' . $url . (str_contains($url, '?') ? '&' : '?') . $msg);
    exit();
}

function collectDetails(array $post): string {
    $exclude = [
        'type_contrat', 'id_categorie', 'id_formule', 'formule', 'formule_habitation',
        'date_debut_contrat', 'date_fin_contrat', 'date_debut', 'date_fin',
        'prime_contrat', 'franchise_contrat', 'statut_contrat'
    ];

    $details = [];
    foreach ($post as $key => $value) {
        if (in_array($key, $exclude, true)) continue;
        if (is_array($value)) {
            $clean = array_values(array_filter(array_map('trim', $value), fn($v) => $v !== ''));
            if (!empty($clean)) $details[$key] = $clean;
        } else {
            $clean = trim((string)$value);
            if ($clean !== '') $details[$key] = $clean;
        }
    }

    return json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contrat.php');
    exit();
}

$controller = new ContratController();
$idClient = (int)($_SESSION['id_user'] ?? $_POST['id_client'] ?? 1);
$type = trim($_POST['type_contrat'] ?? '');
$idCategorie = (int)($_POST['id_categorie'] ?? 0);
$idFormule = (int)($_POST['id_formule'] ?? 0);
$formuleName = trim($_POST['formule'] ?? '');
$return = backToType($type);

if ($type === '' || $idCategorie <= 0 || ($idFormule <= 0 && $formuleName === '')) {
    redirectWith($return, 'error=champs');
}

$formule = null;
if ($idFormule > 0) {
    $formule = $controller->getFormuleById($idFormule);
} else {
    $formule = $controller->getFormuleByNameAndCategorie($formuleName, $idCategorie);
}

if (!$formule || (int)$formule['id_categorie'] !== $idCategorie) {
    redirectWith($return, 'error=formule');
}

// Le client ne remplit pas ces champs : ils sont générés automatiquement.
$dateDebut = date('Y-m-d');
$dateFin = date('Y-m-d', strtotime('+1 year'));
$numero = $controller->generateNumero();
$prime = (float)($formule['prix_formule'] ?? 0);
$franchise = (float)($formule['franchise_formule'] ?? 0);
$formuleContrat = (string)($formule['nom_formule'] ?? $formuleName);
$detailsContrat = collectDetails($_POST);

$contrat = new Contrat(
    $numero,
    $type,
    $idClient,
    $idCategorie,
    $prime,
    $franchise,
    $dateDebut,
    $dateFin,
    'en attente',
    (int)$formule['id_formule'],
    $formuleContrat,
    $detailsContrat
);

$controller->addContrat($contrat);
header('Location: contrat.php?success=demande');
exit();
