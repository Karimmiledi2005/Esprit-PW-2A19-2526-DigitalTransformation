<?php
require_once 'connexion.php';
$db = config::getConnexion();
$stmt = $db->query("DESCRIBE reclamation");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
