<?php
require 'connexion.php';
$db = config::getConnexion();
foreach($db->query('DESCRIBE traitement') as $row) {
    echo $row['Field'] . "\n";
}
