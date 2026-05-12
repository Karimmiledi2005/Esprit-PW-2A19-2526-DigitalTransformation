<?php
require 'connexion.php';
$db = config::getConnexion();
echo "--- ADMIN ---\n";
foreach($db->query('DESCRIBE admin') as $row) echo $row['Field'] . "\n";
echo "--- AGENT ---\n";
foreach($db->query('DESCRIBE agent') as $row) echo $row['Field'] . "\n";
