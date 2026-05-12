<?php
require_once __DIR__ . '/../../controller/FormuleController.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $formuleC = new FormuleController();
    $formuleC->deleteFormule($id);
}

header("Location: formules_back.php");
exit;