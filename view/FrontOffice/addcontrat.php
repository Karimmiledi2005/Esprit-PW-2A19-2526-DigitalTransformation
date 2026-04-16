<?php
require_once '../../controller/contratController.php';
require_once '../../model/contratmodel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero  = trim($_POST['numero_contrat'] ?? '');
    $type    = trim($_POST['type_contrat'] ?? '');
    $debut   = trim($_POST['date_debut'] ?? '');
    $fin     = trim($_POST['date_fin'] ?? '');
    $prime   = $_POST['montant_prime'] ?? '';
    $franc   = $_POST['franchise'] ?? '';
    $statut  = 'en attente'; // Front office: toujours en attente
    $formule = $_POST['formule'] ?? '';
    $details = $_POST['details_formule'] ?? '';

    // Validation serveur
    if (!$numero || !$type || !$debut || !$fin || $prime === '' || $franc === '') {
        header('Location: client.php?msg=erreur');
        exit;
    }
    if ($fin <= $debut) {
        header('Location: client.php?msg=erreur');
        exit;
    }

    $idCategorie = match($type) { 'Auto'=>1, 'Habitation'=>2, 'Sante'=>3, 'Protection'=>4, default=>null };

    $contrat = new Contrat(null, $numero, $type,
        new DateTime($debut), new DateTime($fin),
        (float)$prime, (float)$franc,
        $statut, $idCategorie,
        $formule ?: null, $details ?: null
    );

    $c = new ContratController();
    $c->addContrat($contrat);
    header('Location: client.php?msg=ajoute');
    exit;
}
header('Location: client.php');
