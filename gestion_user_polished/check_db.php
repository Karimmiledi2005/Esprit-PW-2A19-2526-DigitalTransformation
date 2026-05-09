<?php
require_once __DIR__ . '/connexion.php';
$db = config::getConnexion();

function getDesc($db, $table) {
    try {
        echo "\nStructure de $table:\n";
        print_r($db->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC));
    } catch(Exception $e) { echo "Table $table non trouvée.\n"; }
}

getDesc($db, 'user');
getDesc($db, 'admin');
getDesc($db, 'agent');
getDesc($db, 'client');
getDesc($db, 'agence');
