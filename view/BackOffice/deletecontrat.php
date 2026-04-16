<?php
require_once '../../controller/contratController.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $c = new ContratController();
    $c->deleteContrat((int)$_GET['id']);
}
header('Location: contrat.php?msg=supprime');
exit;
