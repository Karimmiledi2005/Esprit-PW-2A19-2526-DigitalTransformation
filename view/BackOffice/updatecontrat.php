<?php
require_once '../../controller/contratController.php';
require_once '../../model/contratmodel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id      = (int)($_POST['id_contrat'] ?? 0);
    $numero  = trim($_POST['numero_contrat'] ?? '');
    $type    = trim($_POST['type_contrat'] ?? '');
    $debut   = trim($_POST['date_debut'] ?? '');
    $fin     = trim($_POST['date_fin'] ?? '');
    $prime   = $_POST['montant_prime'] ?? '';
    $franc   = $_POST['franchise'] ?? '';
    $statut  = $_POST['statut'] ?? 'en attente';
    $formule = $_POST['formule'] ?? '';
    $details = $_POST['details_formule'] ?? '';

    $errors = [];
    if (!$id) $errors[] = "Identifiant du contrat manquant.";
    if (!$numero || !preg_match('/^CTR-\d{4}-\d{3,}$/', $numero)) $errors[] = "Numéro contrat invalide.";
    if (!in_array($type, ['Auto','Sante','Habitation','Protection'])) $errors[] = "Type de contrat invalide.";
    if (!$debut) $errors[] = "Date début obligatoire.";
    if (!$fin)   $errors[] = "Date fin obligatoire.";
    if ($debut && $fin && $fin <= $debut) $errors[] = "Date fin doit être après date début.";

    $minValues = [
        'Auto'       => ['prime' => 120, 'franchise' => 80],
        'Sante'      => ['prime' => 180, 'franchise' => 50],
        'Habitation' => ['prime' => 320, 'franchise' => 150],
        'Protection' => ['prime' => 140, 'franchise' => 70],
    ];
    $minPrime = $minValues[$type]['prime'] ?? 0;
    $minFranchise = $minValues[$type]['franchise'] ?? 0;

    if ($prime === '' || !is_numeric($prime) || (float)$prime < $minPrime) {
        $errors[] = "Montant prime invalide. Minimum {$minPrime} DT pour {$type}.";
    }
    if ($franc === '' || !is_numeric($franc) || (float)$franc < $minFranchise) {
        $errors[] = "Franchise invalide. Minimum {$minFranchise} DT pour {$type}.";
    }

    if (!empty($errors)) {
        session_start();
        $_SESSION['errors'] = $errors;
        $redirect = $_SERVER['HTTP_REFERER'] ?? 'contrat.php';
        $redirect .= (strpos($redirect, '?') === false ? '?msg=erreur' : '&msg=erreur');
        header('Location: ' . $redirect);
        exit;
    }

    $idCategorie = match($type) { 'Auto'=>1, 'Habitation'=>2, 'Sante'=>3, 'Protection'=>4, default=>null };

    $contrat = new Contrat($id, $numero, $type,
        new DateTime($debut), new DateTime($fin),
        $prime, $franc, $statut, $idCategorie,
        $formule ?: null, $details ?: null
    );

    $c = new ContratController();
    $c->updateContrat($contrat, $id);
    header('Location: contrat.php?msg=modifie');
    exit;
}
header('Location: contrat.php');
