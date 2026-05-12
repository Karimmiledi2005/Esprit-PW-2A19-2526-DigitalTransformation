<?php
require 'connexion.php';
$db = config::getConnexion();
foreach($db->query('DESCRIBE user') as $row) {
    echo $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
}
