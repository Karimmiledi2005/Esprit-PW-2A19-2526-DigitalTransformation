<?php
require 'connexion.php';
$db = config::getConnexion();
foreach($db->query('SHOW TABLES') as $row) {
    echo array_values($row)[0] . "\n";
}
