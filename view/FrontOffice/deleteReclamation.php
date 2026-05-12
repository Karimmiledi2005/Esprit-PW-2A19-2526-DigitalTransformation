<?php
require_once __DIR__ . '/../../bootstrap.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['id_user'])) {
	header('Location: ../../login.html');
	exit();
}

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
	$reclamationC = new ReclamationController();
	$reclamationC->deleteReclamation($id);
}

header('Location: reclamationList.php');
exit();
?>
