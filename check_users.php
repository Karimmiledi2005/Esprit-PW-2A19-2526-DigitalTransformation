<?php
require_once 'c:/xampp/htdocs/integrationFINAL/connexion.php';
$db = config::getConnexion();
$res = $db->query("SELECT id_user, nom, prenom FROM user LIMIT 5");
print_r($res->fetchAll(PDO::FETCH_ASSOC));
