<?php
require_once '../../controller/contratController.php';
require_once '../../model/contratmodel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id      = (int)($_POST['id_contrat'] ?? 0);
    $numero  = trim($_POST['numero_contrat'] ?? '');
    $type    = trim($_POST['type_contrat'] ?? '');
    $debut   = trim($_POST['date_debut'] ?? '');
    $fin     = trim($_POST['date_fin'] ?? '');
    $prime   = (float)($_POST['montant_prime'] ?? 0);
    $franc   = (float)($_POST['franchise'] ?? 0);
    $statut  = $_POST['statut'] ?? 'en attente';
    $formule = $_POST['formule'] ?? '';
    $details = $_POST['details_formule'] ?? '';

    if (!$id || !$numero || !$type || !$debut || !$fin) {
        header('Location: client.php?msg=erreur');
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
    header('Location: clientcontrat.php?msg=modifie');
    exit;
}
header('Location: clientcontrat.php');
