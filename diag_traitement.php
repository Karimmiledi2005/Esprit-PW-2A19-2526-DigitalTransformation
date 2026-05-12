<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['role'] = 'agent';
$_SESSION['id_user'] = 103;
$_SESSION['id_agence'] = 1;
$_SESSION['user_id'] = 103; // Compatibilité avec certains scripts qui utilisent user_id

require_once 'connexion.php';
require_once 'helpers/RoleHelper.php';
require_once 'controller/TraitementController.php';

echo "ROLE EN SESSION: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'AUCUN') . "\n";
echo "USER_ID EN SESSION: " . (isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'AUCUN') . "\n";
echo "AGENCE_ID EN SESSION: " . (isset($_SESSION['id_agence']) ? $_SESSION['id_agence'] : 'AUCUN') . "\n\n";

$controller = new TraitementController();
$traitements = $controller->getAllByRole();

echo "NB TRAITEMENTS TROUVES: " . count($traitements) . "\n\n";

foreach ($traitements as $t) {
    echo "ID: " . $t->getIdTraitement() . " | ID_USER: " . $t->getIdUser() . " | NOM_AGENT: " . $t->getNomAgent() . "\n";
}
