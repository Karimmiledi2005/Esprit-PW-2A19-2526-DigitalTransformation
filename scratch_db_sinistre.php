<?php
require 'connexion.php';
$db = config::getConnexion();
foreach($db->query('DESCRIBE sinistre') as $row) {
    echo $row['Field'] . "\n";
}
