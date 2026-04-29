<?php
require_once __DIR__ . '/../../Controller/ReclamationController.php';

$reclamationC = new ReclamationController();
$reclamationC->deleteReclamation($_GET['id']);
header('Location: reclamationList.php');
exit();
?>
