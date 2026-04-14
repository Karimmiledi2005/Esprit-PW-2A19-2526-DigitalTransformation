<?php
require_once '../../Controller/ContratController.php';

if (isset($_GET['id'])) {
    $contratC = new ContratController();
    $contratC->deleteContrat($_GET['id']);
}

header('Location: contrat.php');
?>