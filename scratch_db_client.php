<?php
require 'connexion.php';
$db = config::getConnexion();
foreach($db->query('DESCRIBE client') as $row) {
    echo $row['Field'] . "\n";
}
