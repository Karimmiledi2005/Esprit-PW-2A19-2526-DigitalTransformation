<?php
require 'connexion.php';
$db = config::getConnexion();
foreach($db->query('DESCRIBE contrat') as $row) {
    echo $row['Field'] . "\n";
}
