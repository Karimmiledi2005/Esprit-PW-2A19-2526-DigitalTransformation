<?php
require 'connexion.php';
$db = config::getConnexion();
$db->exec("UPDATE admin SET niveau_acces = 'admin' WHERE niveau_acces = 'admin_agence'");
echo "Base de donnees mise a jour : admin_agence -> admin dans la table 'admin'.\n";
