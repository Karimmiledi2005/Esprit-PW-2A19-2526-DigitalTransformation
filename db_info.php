<?php
require 'connexion.php';
$db = config::getConnexion();
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "Tables:\n";
print_r($tables);

foreach ($tables as $t) {
    if (strpos($t, 'network') !== false || strpos($t, 'friend') !== false || strpos($t, 'contact') !== false || strpos($t, 'message') !== false || strpos($t, 'chat') !== false) {
        echo "\nStructure de $t:\n";
        print_r($db->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC));
    }
}
