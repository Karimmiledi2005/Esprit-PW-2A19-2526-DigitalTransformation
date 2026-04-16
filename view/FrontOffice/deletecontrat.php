<?php
require_once '../../controller/contratController.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $c = new ContratController();
    $c->resilierContrat((int)$_GET['id']);
}
header('Location: client.php?msg=resilie');
exit;
